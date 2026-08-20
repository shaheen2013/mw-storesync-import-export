<?php

namespace MW\WooImportExport\Importer;

if (! defined('ABSPATH')) {
	exit;
}

class OrderProvisioner
{
	public function import_rows(array $rows, $duplicate_handling = 'update')
	{
		if (! function_exists('wc_create_order')) {
			return array('error' => __('WooCommerce is required for order import.', 'mw-order-import-export-sync-for-woocommerce'));
		}

		$result = array(
			'success' => 0,
			'failed'  => 0,
			'skipped' => 0,
			'logs'    => array(),
		);

		// Per-row validation: invalid rows are skipped and reported, valid rows import.

		foreach ($rows as $index => $row) {
			$row_number = $index + 2;
			// Validate this row first. If invalid, mark failed and continue.
			$row_errors = $this->validate_row($row, $row_number);
			if (! empty($row_errors)) {
				++$result['failed'];
				foreach ($row_errors as $err) {
					$result['logs'][] = $err;
				}
				continue;
			}

			$response   = $this->import_row($row, $duplicate_handling);

			if (is_wp_error($response)) {
				++$result['failed'];
				$result['logs'][] = sprintf(
					/* translators: 1: row number, 2: error message */
					__('Row %1$d failed: %2$s', 'mw-order-import-export-sync-for-woocommerce'),
					$row_number,
					$response->get_error_message()
				);
				continue;
			}

			// Response is expected to be an array with 'id', 'is_new' and optional 'skipped'.
			if (is_array($response) && isset($response['id'])) {
				if (! empty($response['skipped'])) {
					++$result['skipped'];
					$result['logs'][] = sprintf(__('Row %1$d skipped existing order #%2$d.', 'mw-order-import-export-sync-for-woocommerce'), $row_number, $response['id']);
					continue;
				}

				++$result['success'];
				if (! empty($response['is_new'])) {
					$result['logs'][] = sprintf(__('Row %1$d imported as order #%2$d.', 'mw-order-import-export-sync-for-woocommerce'), $row_number, $response['id']);
				} else {
					$result['logs'][] = sprintf(__('Row %1$d updated order #%2$d.', 'mw-order-import-export-sync-for-woocommerce'), $row_number, $response['id']);
				}
				continue;
			}

			// Fallback: older behavior returned an ID directly.
			++$result['success'];
			$result['logs'][] = sprintf(__('Row %1$d imported as order #%2$d.', 'mw-order-import-export-sync-for-woocommerce'), $row_number, absint($response));
		}

		if ($result['failed'] > 0) {
			$result['error'] = __('Import completed with errors. Some rows failed.', 'mw-order-import-export-sync-for-woocommerce');
		}

		return $result;
	}

	/**
	 * Validate a single row and return array of error messages for that row.
	 */
	private function validate_row(array $row, $row_number)
	{
		$errors = array();

		// Require at least one customer identifier: billing_email or customer_id.
		$billing_email = isset($row['billing_email']) ? trim($row['billing_email']) : '';
		$customer_id   = isset($row['customer_id']) ? trim($row['customer_id']) : '';

		if ('' === $billing_email && '' === $customer_id) {
			$errors[] = sprintf( /* translators: 1: row number */__('Row %1$d: missing customer identifier (billing_email or customer_id required).', 'mw-order-import-export-sync-for-woocommerce'), $row_number);
		}

		// If order_total is provided, it must be numeric.
		if (isset($row['order_total']) && '' !== trim((string) $row['order_total']) && ! is_numeric($row['order_total'])) {
			$errors[] = sprintf(__('Row %1$d: order_total must be numeric.', 'mw-order-import-export-sync-for-woocommerce'), $row_number);
		}

		if (! empty($row['status']) && ! $this->is_valid_order_status($row['status'])) {
			$errors[] = sprintf(__('Row %1$d: invalid order status.', 'mw-order-import-export-sync-for-woocommerce'), $row_number);
		}

		// Validate line_items JSON shape if present.
		if (! empty($row['line_items'])) {
			$line_items = json_decode($row['line_items'], true);
			if (! is_array($line_items)) {
				$errors[] = sprintf(__('Row %1$d: line_items must be a JSON array.', 'mw-order-import-export-sync-for-woocommerce'), $row_number);
			} else {
				$errors = array_merge($errors, $this->validate_line_items($line_items, $row_number));
			}
		}

		return $errors;
	}

	private function import_row(array $row, $duplicate_handling = 'update')
	{
		// Try to find an existing order.
		$existing_order = $this->find_existing_order($row);

		// If an existing order is found and the rule is 'skip', do not modify it.
		if ($existing_order && 'skip' === $duplicate_handling) {
			return array('id' => $existing_order->get_id(), 'is_new' => false, 'skipped' => true);
		}

		$is_new = false;
		if ($existing_order && 'create_new' !== $duplicate_handling) {
			// Update the existing order.
			$order = $existing_order;
			$is_new = false;
		} else {
			// Create a fresh order (either because none found or the rule forces new creation).
			$order  = wc_create_order();
			$is_new = true;
		}

		if (is_wp_error($order)) {
			return $order;
		}

		try {
			$core_result = $this->apply_core_fields($order, $row);
			if (is_wp_error($core_result)) {
				return $core_result;
			}

			$this->apply_addresses($order, $row);
			$this->apply_meta($order, $row);

			$line_items_result = $this->apply_line_items($order, $row);
			if (is_wp_error($line_items_result)) {
				return $line_items_result;
			}

			$order->update_meta_data('_mw_wie_import_source', 'csv');
			$order->calculate_totals();
			$order->save();
		} catch (\Exception $e) {
			return new \WP_Error('mw_wie_order_import_failed', $e->getMessage());
		}

		return array('id' => $order->get_id(), 'is_new' => $is_new, 'skipped' => false);
	}

	/**
	 * Attempt to find an existing order matching the incoming row.
	 * Order of checks:
	 *  - explicit order_id column
	 *  - transaction_id (stored as _transaction_id meta)
	 */
	private function find_existing_order(array $row)
	{
		if (! empty($row['order_id'])) {
			$order = wc_get_order(absint($row['order_id']));
			if ($order) {
				return $order;
			}
		}

		if (! empty($row['transaction_id'])) {
			$txn = sanitize_text_field($row['transaction_id']);
			$orders = wc_get_orders(array(
				'limit'      => 1,
				'meta_key'   => '_transaction_id',
				'meta_value' => $txn,
				'type'       => 'shop_order',
				'return'     => 'objects',
			));

			if (! empty($orders) && is_array($orders)) {
				return $orders[0];
			}
		}

		// Match by order_number stored in meta (some stores/plugins save this as _order_number).
		if (! empty($row['order_number'])) {
			$number = sanitize_text_field($row['order_number']);
			$orders = wc_get_orders(array(
				'limit'      => 1,
				'meta_key'   => '_order_number',
				'meta_value' => $number,
				'type'       => 'shop_order',
				'return'     => 'objects',
			));

			if (! empty($orders) && is_array($orders)) {
				return $orders[0];
			}
		}

		// Match by billing email + order total as a fallback (useful when exported CSV lacks IDs).
		$billing_email = ! empty($row['billing_email']) ? sanitize_email($row['billing_email']) : '';
		$order_total   = isset($row['order_total']) ? (float) $row['order_total'] : 0;
		if ('' !== $billing_email && $order_total > 0) {
			$orders = wc_get_orders(array(
				'limit'         => 10,
				'billing_email' => $billing_email,
				'type'          => 'shop_order',
				'return'        => 'objects',
			));

			if (! empty($orders) && is_array($orders)) {
				foreach ($orders as $o) {
					// Compare totals numerically with a small tolerance.
					if (abs((float) $o->get_total() - $order_total) < 0.01) {
						return $o;
					}
				}
			}
		}

		return false;
	}

	private function apply_core_fields($order, array $row)
	{
		if (! empty($row['status'])) {
			$status = $this->normalize_order_status($row['status']);
			if ('' === $status) {
				return new \WP_Error('mw_wie_invalid_status', __('Invalid order status.', 'mw-order-import-export-sync-for-woocommerce'));
			}
			$order->set_status($status);
		}

		if (! empty($row['order_currency'])) {
			$order->set_currency(sanitize_text_field($row['order_currency']));
		}

		if (! empty($row['customer_id'])) {
			$order->set_customer_id(absint($row['customer_id']));
		}

		if (! empty($row['customer_note'])) {
			$order->set_customer_note(sanitize_textarea_field($row['customer_note']));
		}

		if (isset($row['payment_method'])) {
			$order->set_payment_method(sanitize_key($row['payment_method']));
		}

		if (isset($row['payment_method_title'])) {
			$order->set_payment_method_title(sanitize_text_field($row['payment_method_title']));
		}

		if (isset($row['transaction_id'])) {
			$order->set_transaction_id(sanitize_text_field($row['transaction_id']));
		}

		return true;
	}

	private function apply_addresses($order, array $row)
	{
		$billing = array(
			'first_name' => $row['billing_first_name'] ?? '',
			'last_name'  => $row['billing_last_name'] ?? '',
			'company'    => $row['billing_company'] ?? '',
			'email'      => $row['billing_email'] ?? ($row['customer_email'] ?? ''),
			'phone'      => $row['billing_phone'] ?? '',
			'address_1'  => $row['billing_address_1'] ?? '',
			'address_2'  => $row['billing_address_2'] ?? '',
			'postcode'   => $row['billing_postcode'] ?? '',
			'city'       => $row['billing_city'] ?? '',
			'state'      => $row['billing_state'] ?? '',
			'country'    => $row['billing_country'] ?? '',
		);

		$shipping = array(
			'first_name' => $row['shipping_first_name'] ?? '',
			'last_name'  => $row['shipping_last_name'] ?? '',
			'company'    => $row['shipping_company'] ?? '',
			'phone'      => $row['shipping_phone'] ?? '',
			'address_1'  => $row['shipping_address_1'] ?? '',
			'address_2'  => $row['shipping_address_2'] ?? '',
			'postcode'   => $row['shipping_postcode'] ?? '',
			'city'       => $row['shipping_city'] ?? '',
			'state'      => $row['shipping_state'] ?? '',
			'country'    => $row['shipping_country'] ?? '',
		);

		$order->set_address(array_map('sanitize_text_field', $billing), 'billing');
		$order->set_address(array_map('sanitize_text_field', $shipping), 'shipping');
	}

	private function apply_meta($order, array $row)
	{
		foreach ($row as $key => $value) {
			if (0 !== strpos($key, 'meta:') || '' === $value) {
				continue;
			}

			$order->update_meta_data(substr($key, 5), sanitize_text_field($value));
		}
	}

	private function apply_line_items($order, array $row)
	{
		if (empty($row['line_items'])) {
			return true;
		}

		$line_items = json_decode($row['line_items'], true);

		if (! is_array($line_items)) {
			return new \WP_Error('mw_wie_invalid_line_items', __('Line items must be a JSON array.', 'mw-order-import-export-sync-for-woocommerce'));
		}

		$line_item_errors = $this->validate_line_items($line_items);
		if (! empty($line_item_errors)) {
			return new \WP_Error('mw_wie_invalid_line_items', implode(' ', $line_item_errors));
		}

		foreach ($order->get_items('line_item') as $item_id => $item) {
			$order->remove_item($item_id);
		}

		foreach ($line_items as $line_item) {
			$product_id = ! empty($line_item['product_id']) ? absint($line_item['product_id']) : 0;
			$product    = $product_id ? wc_get_product($product_id) : false;

			if (! $product && ! empty($line_item['sku'])) {
				$product_id = wc_get_product_id_by_sku(sanitize_text_field($line_item['sku']));
				$product    = $product_id ? wc_get_product($product_id) : false;
			}

			if (! $product) {
				return new \WP_Error('mw_wie_product_not_found', __('A line item product could not be found.', 'mw-order-import-export-sync-for-woocommerce'));
			}

			$quantity = ! empty($line_item['quantity']) ? wc_stock_amount((float) $line_item['quantity']) : 1;
			$order->add_product($product, $quantity);
		}

		return true;
	}

	private function validate_line_items(array $line_items, $row_number = 0)
	{
		$errors = array();

		foreach ($line_items as $index => $item) {
			$item_number = $index + 1;
			$prefix      = $row_number
				? sprintf(__('Row %1$d, line item %2$d:', 'mw-order-import-export-sync-for-woocommerce'), $row_number, $item_number)
				: sprintf(__('Line item %1$d:', 'mw-order-import-export-sync-for-woocommerce'), $item_number);

			if (! is_array($item)) {
				$errors[] = sprintf(__('%1$s item must be an object.', 'mw-order-import-export-sync-for-woocommerce'), $prefix);
				continue;
			}

			$product_id = isset($item['product_id']) ? absint($item['product_id']) : 0;
			$sku        = isset($item['sku']) ? sanitize_text_field($item['sku']) : '';
			$quantity   = isset($item['quantity']) ? $item['quantity'] : 1;

			if (! $product_id && '' === $sku) {
				$errors[] = sprintf(__('%1$s missing product identifier (product_id or sku).', 'mw-order-import-export-sync-for-woocommerce'), $prefix);
			}

			if (! is_numeric($quantity) || (float) $quantity <= 0) {
				$errors[] = sprintf(__('%1$s invalid quantity.', 'mw-order-import-export-sync-for-woocommerce'), $prefix);
			}

			foreach (array('subtotal', 'total') as $amount_key) {
				if (isset($item[$amount_key]) && '' !== $item[$amount_key] && ! is_numeric($item[$amount_key])) {
					$errors[] = sprintf(__('%1$s invalid %2$s value.', 'mw-order-import-export-sync-for-woocommerce'), $prefix, $amount_key);
				}
			}
		}

		return $errors;
	}

	private function is_valid_order_status($status)
	{
		return '' !== $this->normalize_order_status($status);
	}

	private function normalize_order_status($status)
	{
		$status = preg_replace('/^wc-/', '', sanitize_key($status));

		if ('' === $status || ! function_exists('wc_get_order_statuses')) {
			return '';
		}

		$valid_statuses = array_keys(wc_get_order_statuses());

		return in_array('wc-' . $status, $valid_statuses, true) ? $status : '';
	}
}

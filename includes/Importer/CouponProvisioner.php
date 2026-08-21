<?php

namespace MW\WooImportExport\Importer;

if (! defined('ABSPATH')) {
	exit;
}

class CouponProvisioner
{
	public function validate_headers_from_file($file_path)
	{
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct stream read required for header inspection.
		if (! $handle = fopen($file_path, 'r')) {
			return new \WP_Error('mw_wie_coupon_header_error', __('Unable to read the uploaded CSV file.', 'mw-storesync-import-export'));
		}

		$headers = fgetcsv($handle);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct stream close.
		fclose($handle);

		if (empty($headers) || ! is_array($headers)) {
			return new \WP_Error('mw_wie_coupon_header_error', __('Invalid CSV file. Please make sure the file contains a header row.', 'mw-storesync-import-export'));
		}

		$headers = array_map(array($this, 'normalize_header'), $headers);
		$required = $this->get_required_headers();
		$missing = array();

		foreach ($required as $required_header) {
			if (! in_array($required_header, $headers, true)) {
				$missing[] = $this->get_required_header_label($required_header);
			}
		}

		if (! empty($missing)) {
			return new \WP_Error(
				'mw_wie_coupon_missing_columns',
				sprintf(
					/* translators: 1: list of missing coupon column names */
					__('Missing required coupon columns: %1$s.', 'mw-storesync-import-export'),
					implode(', ', $missing)
				)
			);
		}

		return true;
	}

	public function import_rows(array $rows)
	{
		if (! class_exists('WC_Coupon')) {
			return array('error' => __('WooCommerce is required for coupon import.', 'mw-storesync-import-export'));
		}

		$result = array(
			'success' => 0,
			'failed' => 0,
			'logs' => array(),
			'log_items' => array(),
			'total' => count($rows),
		);

		foreach ($rows as $index => $row) {
			$row_number = $index + 2;
			$errors = $this->validate_row($row, $row_number);

			if (! empty($errors)) {
				++$result['failed'];
				foreach ($errors as $error) {
					$result['logs'][] = $error;
					$result['log_items'][] = array('status' => 'error', 'message' => $error);
				}
				continue;
			}

			$response = $this->import_row($row);

			if (is_wp_error($response)) {
				++$result['failed'];
				$message = sprintf(
					/* translators: 1: row number, 2: error message */
					__('Row %1$d failed: %2$s', 'mw-storesync-import-export'),
					$row_number,
					$response->get_error_message()
				);
				$result['logs'][] = $message;
				$result['log_items'][] = array('status' => 'error', 'message' => $message);
				continue;
			}

			++$result['success'];
			$message = sprintf(
				/* translators: 1: row number, 2: coupon ID */
				__('Row %1$d imported coupon #%2$d.', 'mw-storesync-import-export'),
				$row_number,
				absint($response)
			);
			$result['logs'][] = $message;
			$result['log_items'][] = array('status' => 'success', 'message' => $message);
		}

		if ($result['failed'] > 0) {
			$result['error'] = sprintf(
				/* translators: 1: number of successful coupons, 2: number of failed coupons */
				__('Import completed with errors. %1$d coupon row(s) imported successfully and %2$d row(s) failed.', 'mw-storesync-import-export'),
				absint($result['success']),
				absint($result['failed'])
			);
		} elseif ($result['total'] > 0) {
			$result['error'] = '';
		}

		return $result;
	}

	private function validate_row(array $row, $row_number)
	{
		$errors = array();

		$code = isset($row['code']) ? trim((string) $row['code']) : '';
		$discount_type = isset($row['discount_type']) ? trim((string) $row['discount_type']) : '';
		$amount = isset($row['amount']) ? trim((string) $row['amount']) : '';

		if ('' === $code) {
			$errors[] = sprintf(
				/* translators: 1: row number */
				__('Row %1$d: missing coupon code.', 'mw-storesync-import-export'),
				$row_number
			);
		}

		if ('' === $discount_type) {
			$errors[] = sprintf(
				/* translators: 1: row number */
				__('Row %1$d: missing discount type.', 'mw-storesync-import-export'),
				$row_number
			);
		} else {
			$valid_types = array('fixed_cart', 'fixed_product', 'percent', 'free_shipping');
			if (! in_array($discount_type, $valid_types, true)) {
				$errors[] = sprintf(
					/* translators: 1: row number */
					__('Row %1$d: invalid discount type. Allowed values are fixed_cart, fixed_product, percent, free_shipping.', 'mw-storesync-import-export'),
					$row_number
				);
			}
		}

		if ('free_shipping' !== $discount_type && '' === $amount) {
			$errors[] = sprintf(
				/* translators: 1: row number */
				__('Row %1$d: missing amount for non-free shipping coupon.', 'mw-storesync-import-export'),
				$row_number
			);
		}

		if ('' !== $amount && ! is_numeric($amount)) {
			$errors[] = sprintf(
				/* translators: 1: row number */
				__('Row %1$d: amount must be numeric.', 'mw-storesync-import-export'),
				$row_number
			);
		}

		if (isset($row['usage_limit']) && '' !== trim((string) $row['usage_limit']) && ! is_numeric(trim((string) $row['usage_limit']))) {
			$errors[] = sprintf(
				/* translators: 1: row number */
				__('Row %1$d: usage limit must be numeric.', 'mw-storesync-import-export'),
				$row_number
			);
		}

		if (isset($row['date_expires']) && '' !== trim((string) $row['date_expires'])) {
			if (false === strtotime($row['date_expires'])) {
				$errors[] = sprintf(
					/* translators: 1: row number */
					__('Row %1$d: invalid expiry date format.', 'mw-storesync-import-export'),
					$row_number
				);
			}
		}

		return $errors;
	}

	private function import_row(array $row)
	{
		$coupon_id = 0;
		if (! empty($row['coupon_id']) && is_numeric($row['coupon_id'])) {
			$coupon_id = absint($row['coupon_id']);
		}

		$code = isset($row['code']) ? sanitize_text_field($row['code']) : '';

		if (! $coupon_id && '' !== $code && function_exists('wc_get_coupon_id_by_code')) {
			$coupon_id = wc_get_coupon_id_by_code($code);
		}

		if ($coupon_id) {
			$coupon = new \WC_Coupon($coupon_id);
		} else {
			$post_id = wp_insert_post(array(
				'post_title' => $code,
				'post_status' => 'publish',
				'post_type' => 'shop_coupon',
			));

			if (! $post_id || is_wp_error($post_id)) {
				return new \WP_Error('mw_wie_coupon_create_error', __('Unable to create coupon.', 'mw-storesync-import-export'));
			}

			$coupon = new \WC_Coupon($post_id);
		}

		$this->apply_fields($coupon, $row);

		$coupon->save();

		return $coupon->get_id();
	}

	private function apply_fields($coupon, array $row)
	{
		if (! empty($row['code'])) {
			$coupon->set_code(sanitize_text_field($row['code']));
		}

		if (! empty($row['discount_type'])) {
			$coupon->set_discount_type(sanitize_text_field($row['discount_type']));
		}

		if (isset($row['amount']) && '' !== trim((string) $row['amount'])) {
			$coupon->set_amount(sanitize_text_field($row['amount']));
		} elseif (! empty($row['discount_type']) && 'free_shipping' === $row['discount_type']) {
			$coupon->set_amount('0');
		}

		if (isset($row['description'])) {
			$coupon->set_description(wp_kses_post($row['description']));
		}

		if (! empty($row['date_expires'])) {
			$date = strtotime($row['date_expires']);
			if (false !== $date) {
				$coupon->set_date_expires(gmdate('Y-m-d H:i:s', $date));
			}
		}

		if (isset($row['usage_limit'])) {
			$coupon->set_usage_limit(absint($row['usage_limit']));
		}

		if (isset($row['individual_use'])) {
			$coupon->set_individual_use(in_array(strtolower(trim((string) $row['individual_use'])), array('1', 'yes', 'true', 'on'), true));
		}

		if (isset($row['free_shipping'])) {
			$coupon->set_free_shipping(in_array(strtolower(trim((string) $row['free_shipping'])), array('1', 'yes', 'true', 'on'), true));
		}

		if (! empty($row['product_ids'])) {
			$coupon->set_product_ids($this->normalize_comma_list($row['product_ids']));
		}

		if (! empty($row['excluded_product_ids'])) {
			$coupon->set_excluded_product_ids($this->normalize_comma_list($row['excluded_product_ids']));
		}

		if (! empty($row['product_categories'])) {
			$coupon->set_product_categories($this->normalize_comma_list($row['product_categories']));
		}

		if (! empty($row['excluded_product_categories'])) {
			$coupon->set_excluded_product_categories($this->normalize_comma_list($row['excluded_product_categories']));
		}

		if (! empty($row['email_restrictions'])) {
			$coupon->set_email_restrictions($this->normalize_comma_list($row['email_restrictions']));
		}

		if (isset($row['minimum_amount'])) {
			$coupon->set_minimum_amount(sanitize_text_field($row['minimum_amount']));
		}

		if (isset($row['maximum_amount'])) {
			$coupon->set_maximum_amount(sanitize_text_field($row['maximum_amount']));
		}
	}

	private function normalize_comma_list($value)
	{
		if (is_array($value)) {
			return array_map('sanitize_text_field', $value);
		}

		$items = array_filter(array_map('trim', explode(',', (string) $value)));
		return array_map('sanitize_text_field', $items);
	}

	private function get_required_headers()
	{
		return array('code', 'discount_type');
	}

	private function get_required_header_label($header)
	{
		$labels = array(
			'code' => __('Coupon code', 'mw-storesync-import-export'),
			'discount_type' => __('Discount type', 'mw-storesync-import-export'),
		);

		return isset($labels[$header]) ? $labels[$header] : $header;
	}

	private function normalize_header($header)
	{
		return sanitize_key(trim((string) $header));
	}
}

<?php

namespace MW\WooImportExport\Exporter;

if (! defined('ABSPATH')) {
	exit;
}

class FileWriter
{
	public function download_orders_csv(array $columns, array $filters)
	{
		if (! function_exists('wc_get_orders')) {
			wp_die(esc_html__('WooCommerce is required for order export.', 'mw-storesync-import-export'));
		}

		$available_columns = OrderColumns::get_columns();
		$columns           = array_values(array_intersect($columns, array_keys($available_columns)));

		if (empty($columns)) {
			$columns = OrderColumns::get_default_export_columns();
		}

		$limit      = isset($filters['limit']) ? absint($filters['limit']) : 500;
		$limit      = min(max($limit, 1), 5000);
		$offset     = isset($filters['skip']) ? absint($filters['skip']) : 0;
		$delimiter  = CsvValueSanitizer::validate_delimiter(isset($filters['delimiter']) ? $filters['delimiter'] : ',');
		$batch_size = isset($filters['export_batch_size']) ? max(1, absint($filters['export_batch_size'])) : 30;
		$batch_size = min($batch_size, $limit);

		$custom_name = isset($filters['export_filename']) ? trim($filters['export_filename']) : '';
		$file_name   = ('' !== $custom_name) ? sanitize_file_name($custom_name) . '.csv' : 'mw-order-export-' . gmdate('Y-m-d-H-i-s') . '.csv';

		nocache_headers();
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $file_name . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$headers = array();
		foreach ($columns as $column_key) {
			$headers[] = isset($filters['column_mappings'][$column_key]) && '' !== trim($filters['column_mappings'][$column_key])
				? $filters['column_mappings'][$column_key]
				: ($available_columns[$column_key] ?? $column_key);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct output stream required for CSV download.
		$output         = fopen('php://output', 'w');
		$headers_written = false;

		$exported = 0;
		$matched  = false;

		if (! empty($headers)) {
			fputcsv($output, CsvValueSanitizer::sanitize_row($headers), $delimiter);
			$headers_written = true;
		}

		while ($exported < $limit) {
			$batch_exported = $this->write_orders_csv_batch($output, $columns, $filters, $offset, min($batch_size, $limit - $exported));

			if (0 === $batch_exported) {
				break;
			}

			$matched   = true;
			$exported += $batch_exported;
			$offset   += $batch_exported;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct output stream close.
		fclose($output);
		exit;
	}

	public function normalize_columns(array $columns)
	{
		$available_columns = OrderColumns::get_columns();
		$columns           = array_values(array_intersect($columns, array_keys($available_columns)));

		return ! empty($columns) ? $columns : OrderColumns::get_default_export_columns();
	}

	public function get_csv_headers(array $columns, array $custom_names = array())
	{
		$headers = array();

		foreach ($columns as $column) {
			$headers[] = isset($custom_names[$column]) && '' !== trim($custom_names[$column]) ? $custom_names[$column] : $column;
		}

		return $headers;
	}

	public function write_orders_csv_batch($output, array $columns, array $filters, $offset, $batch_size)
	{
		$query  = new QueryBuilder();
		$orders = $query->get_next_batch(absint($offset), absint($batch_size), $filters);

		if (empty($orders)) {
			return 0;
		}

		$delimiter = CsvValueSanitizer::validate_delimiter(isset($filters['delimiter']) ? $filters['delimiter'] : ',');
		$exported  = 0;

		foreach ($orders as $order) {
			$row = array();

			foreach ($columns as $column) {
				$row[] = CsvValueSanitizer::sanitize_value($this->get_column_value($order, $column));
			}

			fputcsv($output, CsvValueSanitizer::sanitize_row($row), $delimiter);
			$order->update_meta_data('_mw_wie_exported', current_time('mysql', true));
			$order->save();
			++$exported;
		}

		return $exported;
	}

	private function get_column_value($order, $column)
	{
		if (0 === strpos($column, 'meta:')) {
			return $this->stringify($order->get_meta(substr($column, 5)));
		}

		switch ($column) {
			case 'order_id':
				return $order->get_id();
			case 'order_number':
				return $order->get_order_number();
			case 'order_date':
				return $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '';
			case 'paid_date':
				return $order->get_date_paid() ? $order->get_date_paid()->date('Y-m-d H:i:s') : '';
			case 'status':
				return $order->get_status();
			case 'order_total':
				return $order->get_total();
			case 'order_currency':
				return $order->get_currency();
			case 'payment_method':
				return $order->get_payment_method();
			case 'payment_method_title':
				return $order->get_payment_method_title();
			case 'transaction_id':
				return $order->get_transaction_id();
			case 'customer_id':
				return $order->get_customer_id();
			case 'customer_email':
			case 'billing_email':
				return $order->get_billing_email();
			case 'customer_note':
				return $order->get_customer_note();
			case 'line_items':
				return wp_json_encode($this->get_line_items($order));
			case 'shipping_items':
				return wp_json_encode($this->get_shipping_items($order));
			case 'coupon_items':
				return wp_json_encode($this->get_coupon_items($order));
			case 'order_notes':
				return wp_json_encode($this->get_order_notes($order));
			default:
				return $this->get_address_value($order, $column);
		}
	}

	private function get_address_value($order, $column)
	{
		$map = array(
			'billing_first_name'  => 'get_billing_first_name',
			'billing_last_name'   => 'get_billing_last_name',
			'billing_company'     => 'get_billing_company',
			'billing_phone'       => 'get_billing_phone',
			'billing_address_1'   => 'get_billing_address_1',
			'billing_address_2'   => 'get_billing_address_2',
			'billing_postcode'    => 'get_billing_postcode',
			'billing_city'        => 'get_billing_city',
			'billing_state'       => 'get_billing_state',
			'billing_country'     => 'get_billing_country',
			'shipping_first_name' => 'get_shipping_first_name',
			'shipping_last_name'  => 'get_shipping_last_name',
			'shipping_company'    => 'get_shipping_company',
			'shipping_phone'      => 'get_shipping_phone',
			'shipping_address_1'  => 'get_shipping_address_1',
			'shipping_address_2'  => 'get_shipping_address_2',
			'shipping_postcode'   => 'get_shipping_postcode',
			'shipping_city'       => 'get_shipping_city',
			'shipping_state'      => 'get_shipping_state',
			'shipping_country'    => 'get_shipping_country',
		);

		if (isset($map[$column]) && is_callable(array($order, $map[$column]))) {
			return $order->{$map[$column]}();
		}

		return '';
	}

	private function get_line_items($order)
	{
		$items = array();

		foreach ($order->get_items('line_item') as $item) {
			$product = $item->get_product();
			$items[] = array(
				'name'       => $item->get_name(),
				'product_id' => $item->get_product_id(),
				'variation_id' => $item->get_variation_id(),
				'sku'        => $product ? $product->get_sku() : '',
				'quantity'   => $item->get_quantity(),
				'subtotal'   => $item->get_subtotal(),
				'total'      => $item->get_total(),
			);
		}

		return $items;
	}

	private function get_shipping_items($order)
	{
		$items = array();

		foreach ($order->get_items('shipping') as $item) {
			$items[] = array(
				'method_title' => $item->get_method_title(),
				'method_id'    => $item->get_method_id(),
				'total'        => $item->get_total(),
			);
		}

		return $items;
	}

	private function get_coupon_items($order)
	{
		$items = array();

		foreach ($order->get_items('coupon') as $item) {
			$items[] = array(
				'code'     => $item->get_code(),
				'discount' => $item->get_discount(),
			);
		}

		return $items;
	}

	private function get_order_notes($order)
	{
		$notes = wc_get_order_notes(
			array(
				'order_id' => $order->get_id(),
				'type'     => 'any',
			)
		);

		return array_map(
			static function ($note) {
				return array(
					'content' => $note->content,
					'date'    => $note->date_created ? $note->date_created->date('Y-m-d H:i:s') : '',
				);
			},
			$notes
		);
	}

	private function stringify($value)
	{
		if (is_scalar($value) || null === $value) {
			return (string) $value;
		}

		return wp_json_encode($value);
	}
}

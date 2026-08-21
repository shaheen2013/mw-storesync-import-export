<?php

namespace MW\WooImportExport\Admin;

use MW\WooImportExport\Exporter\CouponColumns;
use MW\WooImportExport\Exporter\CouponFileWriter;
use MW\WooImportExport\Exporter\FileWriter;
use MW\WooImportExport\Exporter\OrderColumns;
use MW\WooImportExport\Exporter\ProductColumns;
use MW\WooImportExport\Exporter\ProductFileWriter;
use MW\WooImportExport\Exporter\ProductReviewsColumns;
use MW\WooImportExport\Exporter\ProductReviewsFileWriter;
use MW\WooImportExport\Exporter\ProductCategoriesColumns;
use MW\WooImportExport\Exporter\ProductCategoriesFileWriter;
use MW\WooImportExport\Exporter\ProductTagsColumns;
use MW\WooImportExport\Exporter\ProductTagsFileWriter;
use MW\WooImportExport\Exporter\SubscriptionsColumns;
use MW\WooImportExport\Exporter\SubscriptionsFileWriter;
use MW\WooImportExport\Exporter\UserColumns;
use MW\WooImportExport\Exporter\UserFileWriter;
use MW\WooImportExport\Importer\CsvParser;
use MW\WooImportExport\Importer\CouponProvisioner;
use MW\WooImportExport\Importer\OrderProvisioner;
use MW\WooImportExport\Importer\ProductProvisioner;
use MW\WooImportExport\Importer\ProductReviewsProvisioner;

if (! defined('ABSPATH')) {
	exit;
}

class Dashboard
{
	public function __construct()
	{
		add_action('admin_menu', array($this, 'register_menu'));
		add_action('admin_post_mw_wie_export_orders', array($this, 'handle_export'));
		add_action('admin_post_mw_wie_import_orders', array($this, 'handle_import'));
		add_action('admin_post_mw_wie_download_export', array($this, 'handle_export_download'));
		add_action('wp_ajax_mw_wie_start_export', array($this, 'ajax_start_export'));
		add_action('wp_ajax_mw_wie_export_batch', array($this, 'ajax_export_batch'));
	}

	public function register_menu()
	{
		if (! class_exists('WooCommerce')) {
			return;
		}

		add_menu_page(
			__('StoreSync Import Export', 'mw-storesync-import-export'),
			__('StoreSync', 'mw-storesync-import-export'),
			'manage_woocommerce',
			'mw-storesync-import-export',
			array($this, 'render'),
			'dashicons-database-import',
			56
		);
	}

	public function render()
	{
		if (! current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('You do not have permission to access this page.', 'mw-storesync-import-export'));
		}

		$columns                         = OrderColumns::get_columns();
		$default_cols                    = OrderColumns::get_default_export_columns();
		$product_columns                 = ProductColumns::get_columns();
		$product_default_cols            = ProductColumns::get_default_export_columns();
		$coupon_columns                  = CouponColumns::get_columns();
		$coupon_default_cols             = CouponColumns::get_default_export_columns();
		$product_reviews_columns         = ProductReviewsColumns::get_columns();
		$product_reviews_default_cols    = ProductReviewsColumns::get_default_export_columns();
		$product_categories_columns      = ProductCategoriesColumns::get_columns();
		$product_categories_default_cols = ProductCategoriesColumns::get_default_export_columns();
		$product_tags_columns            = ProductTagsColumns::get_columns();
		$product_tags_default_cols       = ProductTagsColumns::get_default_export_columns();
		$subscriptions_columns           = SubscriptionsColumns::get_columns();
		$subscriptions_default_cols      = SubscriptionsColumns::get_default_export_columns();
		$user_columns                    = UserColumns::get_columns();
		$user_default_cols               = UserColumns::get_default_export_columns();

		$order_statuses = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : array();
		$import_result  = get_transient('mw_wie_tr_import_result_' . get_current_user_id());
		delete_transient('mw_wie_tr_import_result_' . get_current_user_id());

		include MW_WIE_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}

	public function handle_export()
	{
		if (! current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('Forbidden.', 'mw-storesync-import-export'), 403);
		}

		check_admin_referer('mw_wie_export_orders');

		$export_type   = isset($_POST['export_type']) ? sanitize_key(wp_unslash($_POST['export_type'])) : 'order';

		$export_method     = isset($_POST['export_method']) ? sanitize_key(wp_unslash($_POST['export_method'])) : 'quick';
		$status            = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';
		$limit             = isset($_POST['limit']) ? absint(wp_unslash($_POST['limit'])) : 500;
		$skip              = isset($_POST['skip']) ? absint(wp_unslash($_POST['skip'])) : 0;
		$order_ids         = isset($_POST['order_ids']) ? sanitize_text_field(wp_unslash($_POST['order_ids'])) : '';
		$product           = isset($_POST['product']) ? sanitize_text_field(wp_unslash($_POST['product'])) : '';
		$customer          = isset($_POST['customer']) ? sanitize_text_field(wp_unslash($_POST['customer'])) : '';
		$coupons           = isset($_POST['coupons']) ? sanitize_text_field(wp_unslash($_POST['coupons'])) : '';
		$date_from         = isset($_POST['date_from']) ? sanitize_text_field(wp_unslash($_POST['date_from'])) : '';
		$date_to           = isset($_POST['date_to']) ? sanitize_text_field(wp_unslash($_POST['date_to'])) : '';
		$user_roles        = isset($_POST['user_roles']) ? sanitize_text_field(wp_unslash($_POST['user_roles'])) : '';
		$column_names      = isset($_POST['column_names']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['column_names'])) : array();
		$export_filename   = isset($_POST['export_filename']) ? sanitize_file_name(wp_unslash($_POST['export_filename'])) : '';
		$export_batch_size = isset($_POST['export_batch_size']) ? absint(wp_unslash($_POST['export_batch_size'])) : 30;
		$delimiter         = isset($_POST['delimiter']) ? sanitize_text_field(wp_unslash($_POST['delimiter'])) : ',';
		if ('other' === $delimiter && isset($_POST['custom_delimiter']) && '' !== trim(sanitize_text_field(wp_unslash($_POST['custom_delimiter'])))) {
			$delimiter = sanitize_text_field(wp_unslash($_POST['custom_delimiter']));
		}
		if (! in_array($delimiter, array(',', ';', "\t", '|'), true)) {
			$delimiter = ',';
		}
		$stars             = isset($_POST['stars']) ? absint(wp_unslash($_POST['stars'])) : 0;

		$limit = min(max($limit, 1), 5000);

		if ('quick' === $export_method) {
			switch ($export_type) {
				case 'coupon':
					$columns = CouponColumns::get_default_export_columns();
					break;
				case 'product':
					$columns = ProductColumns::get_default_export_columns();
					break;
				case 'product_reviews':
					$columns = ProductReviewsColumns::get_default_export_columns();
					break;
				case 'product_categories':
					$columns = ProductCategoriesColumns::get_default_export_columns();
					break;
				case 'product_tags':
					$columns = ProductTagsColumns::get_default_export_columns();
					break;
				case 'subscriptions':
					$columns = SubscriptionsColumns::get_default_export_columns();
					break;
				case 'user':
					$columns = UserColumns::get_default_export_columns();
					break;
				default:
					$columns = OrderColumns::get_default_export_columns();
					break;
			}
		} else {
			$columns = isset($_POST['columns']) ? array_map('sanitize_key', (array) wp_unslash($_POST['columns'])) : array();
		}

		$filters = array(
			'export_method'     => $export_method,
			'status'            => $status,
			'limit'             => $limit,
			'skip'              => $skip,
			'order_ids'         => $order_ids,
			'product'           => $product,
			'customer'          => $customer,
			'coupons'           => $coupons,
			'date_from'         => $date_from,
			'date_to'           => $date_to,
			'user_roles'        => $user_roles,
			'column_names'      => $column_names,
			'export_filename'   => $export_filename,
			'export_batch_size' => max(1, $export_batch_size),
			'delimiter'         => ! empty($delimiter) ? $delimiter : ',',
			'stars'             => $stars,
		);

		if ('coupon' === $export_type) {
			$writer = new CouponFileWriter();
			$writer->download_coupons_csv($columns, $filters);
			return;
		}

		if ('product' === $export_type) {
			$writer = new ProductFileWriter();
			$writer->download_products_csv($columns, $filters);
			return;
		}

		if ('product_reviews' === $export_type) {
			$writer = new ProductReviewsFileWriter();
			$writer->download_reviews_csv($columns, $filters);
			return;
		}

		if ('product_categories' === $export_type) {
			$writer = new ProductCategoriesFileWriter();
			$writer->download_categories_csv($columns, $filters);
			return;
		}

		if ('product_tags' === $export_type) {
			$writer = new ProductTagsFileWriter();
			$writer->download_tags_csv($columns, $filters);
			return;
		}

		if ('subscriptions' === $export_type) {
			$writer = new SubscriptionsFileWriter();
			$writer->download_subscriptions_csv($columns, $filters);
			return;
		}

		if ('user' === $export_type) {
			$writer = new UserFileWriter();
			$writer->download_users_csv($columns, $filters);
			return;
		}

		$writer = new FileWriter();
		$writer->download_orders_csv($columns, $filters);
	}

	public function ajax_start_export()
	{
		if (! current_user_can('manage_woocommerce')) {
			wp_send_json_error(array('message' => __('Forbidden.', 'mw-storesync-import-export')), 403);
		}

		check_ajax_referer('mw_wie_export_orders');

		$request = $this->get_export_request_data($_POST);

		if ('order' !== $request['export_type']) {
			wp_send_json_error(array('message' => __('Batch progress export is currently available for order exports.', 'mw-storesync-import-export')), 400);
		}

		$export_dir = $this->get_export_directory();
		if (is_wp_error($export_dir)) {
			wp_send_json_error(array('message' => $export_dir->get_error_message()), 500);
		}

		$job_id    = wp_generate_uuid4();
		$file_name = $this->get_export_file_name($request['filters'], 'mw-order-export');
		$file_path = trailingslashit($export_dir) . $job_id . '-' . $file_name;
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct file stream required for fputcsv.
		$output    = fopen($file_path, 'wb');

		if (! $output) {
			wp_send_json_error(array('message' => __('Unable to create the export file. Please check upload directory permissions.', 'mw-storesync-import-export')), 500);
		}

		$writer  = new FileWriter();
		$headers = $writer->get_csv_headers($request['columns'], isset($request['filters']['column_names']) ? $request['filters']['column_names'] : array());
		fputcsv($output, \MW\WooImportExport\Exporter\CsvValueSanitizer::sanitize_row($headers), $request['filters']['delimiter']);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct file stream required for fputcsv.
		fclose($output);

		$job = array(
			'user_id'      => get_current_user_id(),
			'file_path'    => $file_path,
			'file_name'    => $file_name,
			'columns'      => $request['columns'],
			'filters'      => $request['filters'],
			'offset'       => absint($request['filters']['skip']),
			'exported'     => 0,
			'limit'        => absint($request['filters']['limit']),
			'batch_size'   => absint($request['filters']['export_batch_size']),
			'download_url' => '',
		);

		set_transient($this->get_export_job_key($job_id), $job, HOUR_IN_SECONDS);

		wp_send_json_success(array(
			'job_id'     => $job_id,
			'exported'   => 0,
			'limit'      => $job['limit'],
			'percentage' => 0,
			'message'    => __('Export started.', 'mw-storesync-import-export'),
		));
	}

	public function ajax_export_batch()
	{
		if (! current_user_can('manage_woocommerce')) {
			wp_send_json_error(array('message' => __('Forbidden.', 'mw-storesync-import-export')), 403);
		}

		check_ajax_referer('mw_wie_export_orders');

		$job_id = isset($_POST['job_id']) ? sanitize_key(wp_unslash($_POST['job_id'])) : '';
		$job    = $job_id ? get_transient($this->get_export_job_key($job_id)) : false;

		if (empty($job) || ! is_array($job) || absint($job['user_id']) !== get_current_user_id()) {
			wp_send_json_error(array('message' => __('The export job expired. Please start the export again.', 'mw-storesync-import-export')), 404);
		}

		if (empty($job['file_path']) || ! file_exists($job['file_path'])) {
			delete_transient($this->get_export_job_key($job_id));
			wp_send_json_error(array('message' => __('The export file is missing. Please start the export again.', 'mw-storesync-import-export')), 404);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct file stream required for fputcsv.
		$output = fopen($job['file_path'], 'ab');

		if (! $output) {
			wp_send_json_error(array('message' => __('Unable to write to the export file.', 'mw-storesync-import-export')), 500);
		}

		$remaining      = max(0, absint($job['limit']) - absint($job['exported']));
		$current_batch  = min(absint($job['batch_size']), $remaining);
		$batch_exported = 0;

		if ($current_batch > 0) {
			$writer         = new FileWriter();
			$batch_exported = $writer->write_orders_csv_batch($output, $job['columns'], $job['filters'], $job['offset'], $current_batch);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct file stream required for fputcsv.
		fclose($output);

		$job['exported'] += $batch_exported;
		$job['offset']   += $batch_exported;
		$done             = ($batch_exported <= 0 || $job['exported'] >= $job['limit']);

		if ($done) {
			$download_nonce      = wp_create_nonce('mw_wie_download_export_' . $job_id);
			$job['download_url'] = add_query_arg(
				array(
					'action'   => 'mw_wie_download_export',
					'job_id'   => rawurlencode($job_id),
					'_wpnonce' => $download_nonce,
				),
				admin_url('admin-post.php')
			);
		}

		set_transient($this->get_export_job_key($job_id), $job, HOUR_IN_SECONDS);

		$percentage = $job['limit'] > 0 ? min(100, (int) floor(($job['exported'] / $job['limit']) * 100)) : 100;
		if ($done) {
			$percentage = 100;
		}

		wp_send_json_success(array(
			'job_id'       => $job_id,
			'exported'     => absint($job['exported']),
			'limit'        => absint($job['limit']),
			'percentage'   => $percentage,
			'done'         => $done,
			'download_url' => $job['download_url'],
			'message'      => $done ? __('Export complete.', 'mw-storesync-import-export') : __('Exporting...', 'mw-storesync-import-export'),
		));
	}

	public function handle_export_download()
	{
		if (! current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('Forbidden.', 'mw-storesync-import-export'), 403);
		}

		$job_id = isset($_GET['job_id']) ? sanitize_key(wp_unslash($_GET['job_id'])) : '';

		if ('' === $job_id) {
			wp_die(esc_html__('Missing export job.', 'mw-storesync-import-export'), 400);
		}

		check_admin_referer('mw_wie_download_export_' . $job_id);

		$job = get_transient($this->get_export_job_key($job_id));

		if (empty($job) || ! is_array($job) || absint($job['user_id']) !== get_current_user_id()) {
			wp_die(esc_html__('The export job expired. Please start the export again.', 'mw-storesync-import-export'), 404);
		}

		if (empty($job['file_path']) || ! file_exists($job['file_path'])) {
			delete_transient($this->get_export_job_key($job_id));
			wp_die(esc_html__('The export file is missing. Please start the export again.', 'mw-storesync-import-export'), 404);
		}

		nocache_headers();
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . sanitize_file_name($job['file_name']) . '"');
		header('Content-Length: ' . filesize($job['file_path']));

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Direct output stream required for file download.
		readfile($job['file_path']);
		wp_delete_file($job['file_path']);
		delete_transient($this->get_export_job_key($job_id));
		exit;
	}

	public function handle_import()
	{
		if (! current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('Forbidden.', 'mw-storesync-import-export'), 403);
		}

		check_admin_referer('mw_wie_import_orders');

		if (empty($_FILES['mw_wie_import_file']['tmp_name'])) {
			$this->redirect_with_import_result(array('error' => __('Please choose a valid uploaded CSV file.', 'mw-storesync-import-export')));
			return;
		}

		$tmp_name = isset($_FILES['mw_wie_import_file']['tmp_name']) ? sanitize_text_field(wp_unslash($_FILES['mw_wie_import_file']['tmp_name'])) : '';

		if ('' === $tmp_name || ! is_uploaded_file($tmp_name)) {
			$this->redirect_with_import_result(array('error' => __('Please choose a valid uploaded CSV file.', 'mw-storesync-import-export')));
			return;
		}

		$file_error = isset($_FILES['mw_wie_import_file']['error']) ? absint($_FILES['mw_wie_import_file']['error']) : 0;

		if ($file_error > 0) {
			$this->redirect_with_import_result(array('error' => __('The upload failed. Please try again.', 'mw-storesync-import-export')));
			return;
		}

		$max_upload_size = 10 * MB_IN_BYTES;
		$file_size       = isset($_FILES['mw_wie_import_file']['size']) ? absint($_FILES['mw_wie_import_file']['size']) : 0;

		if ($file_size <= 0 || $file_size > $max_upload_size) {
			$this->redirect_with_import_result(array('error' => __('The CSV file is empty or larger than the allowed 10 MB limit.', 'mw-storesync-import-export')));
			return;
		}

		$file_name = isset($_FILES['mw_wie_import_file']['name']) ? sanitize_file_name(wp_unslash($_FILES['mw_wie_import_file']['name'])) : '';
		$file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

		if ('csv' !== $file_ext) {
			$this->redirect_with_import_result(array('error' => __('Only CSV files are supported in this version.', 'mw-storesync-import-export')));
			return;
		}

		if (! $this->is_valid_csv_upload($tmp_name)) {
			$this->redirect_with_import_result(array('error' => __('The uploaded file content does not appear to be a valid CSV file.', 'mw-storesync-import-export')));
			return;
		}

		$parser      = new CsvParser();
		$rows        = $parser->parse_uploaded_file($tmp_name);
		$import_type = isset($_POST['import_type']) ? sanitize_key(wp_unslash($_POST['import_type'])) : 'order';

		$duplicate_handling = isset($_POST['duplicate_handling']) ? sanitize_key(wp_unslash($_POST['duplicate_handling'])) : 'update';

		if ('coupon' === $import_type) {
			$validator = new CouponProvisioner();
			$header_check = $validator->validate_headers_from_file($tmp_name);
			if (is_wp_error($header_check)) {
				$this->redirect_with_import_result(array(
					'error' => __('Coupon import could not start because the CSV is missing required columns.', 'mw-storesync-import-export'),
					'logs' => array($header_check->get_error_message()),
				));
			}
			$provisioner = $validator;
		} elseif ('product' === $import_type) {
			$provisioner = new ProductProvisioner();
		} elseif ('product_reviews' === $import_type) {
			$provisioner = new ProductReviewsProvisioner();
		} else {
			$provisioner = new OrderProvisioner();
		}

		if ('order' === $import_type) {
			$result = $provisioner->import_rows($rows, $duplicate_handling);
		} else {
			$result = $provisioner->import_rows($rows);
		}

		$this->redirect_with_import_result($result);
	}

	private function redirect_with_import_result($result)
	{
		set_transient('mw_wie_tr_import_result_' . get_current_user_id(), $result, MINUTE_IN_SECONDS);
		wp_safe_redirect(admin_url('admin.php?page=mw-storesync-import-export'));
		exit;
	}

	private function is_valid_csv_upload($path)
	{
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct binary stream read for uploaded tmp file validation.
		$handle = fopen($path, 'rb');

		if (! $handle) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread -- Reading initial bytes to inspect character encoding and mime.
		$bytes = fread($handle, 512);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct stream close.
		fclose($handle);

		if (false === $bytes || '' === $bytes || false !== strpos($bytes, "\0")) {
			return false;
		}

		$bytes = preg_replace('/^\xEF\xBB\xBF/', '', $bytes);

		return 1 === preg_match('/^[\x09\x0A\x0D\x20-\x7E\x80-\xFF]/', $bytes);
	}

	/**
	 * Parse and sanitize all export-related fields from a POST array.
	 *
	 * @param  array $post  Raw $_POST data.
	 * @return array        Associative array with 'export_type', 'columns', and 'filters'.
	 */
	private function get_export_request_data(array $post)
	{
		$export_type   = isset($post['export_type'])   ? sanitize_key(wp_unslash($post['export_type']))   : 'order';
		$export_method = isset($post['export_method']) ? sanitize_key(wp_unslash($post['export_method'])) : 'quick';
		$status        = isset($post['status'])        ? sanitize_key(wp_unslash($post['status']))        : '';
		$limit         = isset($post['limit'])         ? absint(wp_unslash($post['limit']))               : 500;
		$skip          = isset($post['skip'])          ? absint(wp_unslash($post['skip']))                : 0;
		$order_ids     = isset($post['order_ids'])     ? sanitize_text_field(wp_unslash($post['order_ids'])) : '';
		$product       = isset($post['product'])       ? sanitize_text_field(wp_unslash($post['product'])) : '';
		$customer      = isset($post['customer'])      ? sanitize_text_field(wp_unslash($post['customer'])) : '';
		$coupons       = isset($post['coupons'])       ? sanitize_text_field(wp_unslash($post['coupons'])) : '';
		$date_from     = isset($post['date_from'])     ? sanitize_text_field(wp_unslash($post['date_from'])) : '';
		$date_to       = isset($post['date_to'])       ? sanitize_text_field(wp_unslash($post['date_to'])) : '';
		$user_roles    = isset($post['user_roles'])    ? sanitize_text_field(wp_unslash($post['user_roles'])) : '';
		$column_names  = isset($post['column_names'])  ? array_map('sanitize_text_field', (array) wp_unslash($post['column_names'])) : array();
		$export_filename   = isset($post['export_filename'])   ? sanitize_file_name(wp_unslash($post['export_filename'])) : '';
		$export_batch_size = isset($post['export_batch_size']) ? absint(wp_unslash($post['export_batch_size'])) : 30;
		$stars             = isset($post['stars'])             ? absint(wp_unslash($post['stars']))             : 0;

		$delimiter = isset($post['delimiter']) ? sanitize_text_field(wp_unslash($post['delimiter'])) : ',';
		if ('other' === $delimiter && isset($post['custom_delimiter']) && '' !== trim(sanitize_text_field(wp_unslash($post['custom_delimiter'])))) {
			$delimiter = sanitize_text_field(wp_unslash($post['custom_delimiter']));
		}
		$delimiter = \MW\WooImportExport\Exporter\CsvValueSanitizer::validate_delimiter($delimiter);

		$limit = min(max($limit, 1), 5000);

		if ('quick' === $export_method) {
			$columns = \MW\WooImportExport\Exporter\OrderColumns::get_default_export_columns();
		} else {
			$columns = isset($post['columns']) ? array_map('sanitize_key', (array) wp_unslash($post['columns'])) : array();
		}

		$filters = array(
			'export_method'     => $export_method,
			'status'            => $status,
			'limit'             => $limit,
			'skip'              => $skip,
			'order_ids'         => $order_ids,
			'product'           => $product,
			'customer'          => $customer,
			'coupons'           => $coupons,
			'date_from'         => $date_from,
			'date_to'           => $date_to,
			'user_roles'        => $user_roles,
			'column_names'      => $column_names,
			'export_filename'   => $export_filename,
			'export_batch_size' => max(1, $export_batch_size),
			'delimiter'         => $delimiter,
			'stars'             => $stars,
		);

		return array(
			'export_type' => $export_type,
			'columns'     => $columns,
			'filters'     => $filters,
		);
	}

	/**
	 * Return a writable directory path inside the WP uploads folder for temporary
	 * export files. Creates the directory and drops an index.php stub if needed.
	 *
	 * @return string|\WP_Error  Absolute path on success, WP_Error on failure.
	 */
	private function get_export_directory()
	{
		$upload_dir = wp_upload_dir();

		if (! empty($upload_dir['error'])) {
			return new \WP_Error('mw_wie_upload_dir', $upload_dir['error']);
		}

		$export_dir = trailingslashit($upload_dir['basedir']) . 'mw-wie-exports';

		if (! is_dir($export_dir)) {
			if (! wp_mkdir_p($export_dir)) {
				return new \WP_Error(
					'mw_wie_mkdir',
					__('Unable to create the export directory. Please check your server permissions.', 'mw-storesync-import-export')
				);
			}
		}

		// Protect the export directory from direct browsing.
		$index_file = trailingslashit($export_dir) . 'index.php';
		if (! file_exists($index_file)) {
			file_put_contents($index_file, "<?php\n// Silence is golden.\n"); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		$htaccess = trailingslashit($export_dir) . '.htaccess';
		if (! file_exists($htaccess)) {
			file_put_contents($htaccess, "deny from all\n"); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		return $export_dir;
	}

	/**
	 * Generate a timestamped CSV filename for an export job.
	 *
	 * @param  array  $filters  Export filters (may contain 'export_filename').
	 * @param  string $prefix   Fallback filename prefix (e.g. 'mw-order-export').
	 * @return string           Sanitized filename including .csv extension.
	 */
	private function get_export_file_name(array $filters, $prefix = 'mw-export')
	{
		$custom = isset($filters['export_filename']) ? trim($filters['export_filename']) : '';

		if ('' !== $custom) {
			return sanitize_file_name($custom) . '.csv';
		}

		return sanitize_file_name($prefix) . '-' . gmdate('Y-m-d-H-i-s') . '.csv';
	}

	/**
	 * Return the transient key used to store an export job's state.
	 *
	 * @param  string $job_id  UUID identifying the export job.
	 * @return string          Transient key string.
	 */
	private function get_export_job_key($job_id)
	{
		return 'mw_wie_export_job_' . sanitize_key($job_id);
	}
}

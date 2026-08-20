<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SubscriptionsColumns {
	public static function get_columns() {
		$columns = array(
			'subscription_id'    => __( 'Subscription ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'status'             => __( 'Status', 'mw-order-import-export-sync-for-woocommerce' ),
			'customer_id'        => __( 'Customer ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_email'      => __( 'Billing Email', 'mw-order-import-export-sync-for-woocommerce' ),
			'parent_order_id'    => __( 'Parent Order ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_first_name' => __( 'Billing First Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_last_name'  => __( 'Billing Last Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_name'       => __( 'Product Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_sku'        => __( 'Product SKU', 'mw-order-import-export-sync-for-woocommerce' ),
			'total'              => __( 'Total Amount', 'mw-order-import-export-sync-for-woocommerce' ),
			'order_currency'     => __( 'Currency', 'mw-order-import-export-sync-for-woocommerce' ),
			'date_created'       => __( 'Start Date', 'mw-order-import-export-sync-for-woocommerce' ),
			'next_payment_date'  => __( 'Next Payment Date', 'mw-order-import-export-sync-for-woocommerce' ),
			'end_date'           => __( 'End Date', 'mw-order-import-export-sync-for-woocommerce' ),
			'payment_method'     => __( 'Payment Method', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		return apply_filters( 'mw_wie_subscriptions_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

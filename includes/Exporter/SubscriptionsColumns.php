<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SubscriptionsColumns {
	public static function get_columns() {
		$columns = array(
			'subscription_id'    => __( 'Subscription ID', 'mw-storesync-import-export' ),
			'status'             => __( 'Status', 'mw-storesync-import-export' ),
			'customer_id'        => __( 'Customer ID', 'mw-storesync-import-export' ),
			'billing_email'      => __( 'Billing Email', 'mw-storesync-import-export' ),
			'parent_order_id'    => __( 'Parent Order ID', 'mw-storesync-import-export' ),
			'billing_first_name' => __( 'Billing First Name', 'mw-storesync-import-export' ),
			'billing_last_name'  => __( 'Billing Last Name', 'mw-storesync-import-export' ),
			'product_name'       => __( 'Product Name', 'mw-storesync-import-export' ),
			'product_sku'        => __( 'Product SKU', 'mw-storesync-import-export' ),
			'total'              => __( 'Total Amount', 'mw-storesync-import-export' ),
			'order_currency'     => __( 'Currency', 'mw-storesync-import-export' ),
			'date_created'       => __( 'Start Date', 'mw-storesync-import-export' ),
			'next_payment_date'  => __( 'Next Payment Date', 'mw-storesync-import-export' ),
			'end_date'           => __( 'End Date', 'mw-storesync-import-export' ),
			'payment_method'     => __( 'Payment Method', 'mw-storesync-import-export' ),
		);

		return apply_filters( 'mw_wie_subscriptions_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OrderColumns {
	public static function get_columns() {
		$columns = array(
			'order_id'              => __( 'Order ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'order_number'          => __( 'Order number', 'mw-order-import-export-sync-for-woocommerce' ),
			'order_date'            => __( 'Order date', 'mw-order-import-export-sync-for-woocommerce' ),
			'paid_date'             => __( 'Paid date', 'mw-order-import-export-sync-for-woocommerce' ),
			'status'                => __( 'Status', 'mw-order-import-export-sync-for-woocommerce' ),
			'order_total'           => __( 'Order total', 'mw-order-import-export-sync-for-woocommerce' ),
			'order_currency'        => __( 'Order currency', 'mw-order-import-export-sync-for-woocommerce' ),
			'payment_method'        => __( 'Payment method', 'mw-order-import-export-sync-for-woocommerce' ),
			'payment_method_title'  => __( 'Payment method title', 'mw-order-import-export-sync-for-woocommerce' ),
			'transaction_id'        => __( 'Transaction ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'customer_id'           => __( 'Customer ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'customer_email'        => __( 'Customer email', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_first_name'    => __( 'Billing first name', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_last_name'     => __( 'Billing last name', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_company'       => __( 'Billing company', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_email'         => __( 'Billing email', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_phone'         => __( 'Billing phone', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_address_1'     => __( 'Billing address 1', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_address_2'     => __( 'Billing address 2', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_postcode'      => __( 'Billing postcode', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_city'          => __( 'Billing city', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_state'         => __( 'Billing state', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_country'       => __( 'Billing country', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_first_name'   => __( 'Shipping first name', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_last_name'    => __( 'Shipping last name', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_company'      => __( 'Shipping company', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_phone'        => __( 'Shipping phone', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_address_1'    => __( 'Shipping address 1', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_address_2'    => __( 'Shipping address 2', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_postcode'     => __( 'Shipping postcode', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_city'         => __( 'Shipping city', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_state'        => __( 'Shipping state', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_country'      => __( 'Shipping country', 'mw-order-import-export-sync-for-woocommerce' ),
			'customer_note'         => __( 'Customer note', 'mw-order-import-export-sync-for-woocommerce' ),
			'line_items'            => __( 'Line items', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_items'        => __( 'Shipping items', 'mw-order-import-export-sync-for-woocommerce' ),
			'coupon_items'          => __( 'Coupon items', 'mw-order-import-export-sync-for-woocommerce' ),
			'order_notes'           => __( 'Order notes', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '8.5', '>=' ) ) {
			$columns['meta:_wc_order_attribution_source_type'] = __( 'WC attribution source type', 'mw-order-import-export-sync-for-woocommerce' );
			$columns['meta:_wc_order_attribution_utm_source']  = __( 'WC attribution UTM source', 'mw-order-import-export-sync-for-woocommerce' );
			$columns['meta:_wc_order_attribution_referrer']    = __( 'WC attribution referrer', 'mw-order-import-export-sync-for-woocommerce' );
		}

		return apply_filters( 'mw_wie_order_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}


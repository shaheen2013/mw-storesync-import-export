<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OrderColumns {
	public static function get_columns() {
		$columns = array(
			'order_id'              => __( 'Order ID', 'mw-storesync-import-export' ),
			'order_number'          => __( 'Order number', 'mw-storesync-import-export' ),
			'order_date'            => __( 'Order date', 'mw-storesync-import-export' ),
			'paid_date'             => __( 'Paid date', 'mw-storesync-import-export' ),
			'status'                => __( 'Status', 'mw-storesync-import-export' ),
			'order_total'           => __( 'Order total', 'mw-storesync-import-export' ),
			'order_currency'        => __( 'Order currency', 'mw-storesync-import-export' ),
			'payment_method'        => __( 'Payment method', 'mw-storesync-import-export' ),
			'payment_method_title'  => __( 'Payment method title', 'mw-storesync-import-export' ),
			'transaction_id'        => __( 'Transaction ID', 'mw-storesync-import-export' ),
			'customer_id'           => __( 'Customer ID', 'mw-storesync-import-export' ),
			'customer_email'        => __( 'Customer email', 'mw-storesync-import-export' ),
			'billing_first_name'    => __( 'Billing first name', 'mw-storesync-import-export' ),
			'billing_last_name'     => __( 'Billing last name', 'mw-storesync-import-export' ),
			'billing_company'       => __( 'Billing company', 'mw-storesync-import-export' ),
			'billing_email'         => __( 'Billing email', 'mw-storesync-import-export' ),
			'billing_phone'         => __( 'Billing phone', 'mw-storesync-import-export' ),
			'billing_address_1'     => __( 'Billing address 1', 'mw-storesync-import-export' ),
			'billing_address_2'     => __( 'Billing address 2', 'mw-storesync-import-export' ),
			'billing_postcode'      => __( 'Billing postcode', 'mw-storesync-import-export' ),
			'billing_city'          => __( 'Billing city', 'mw-storesync-import-export' ),
			'billing_state'         => __( 'Billing state', 'mw-storesync-import-export' ),
			'billing_country'       => __( 'Billing country', 'mw-storesync-import-export' ),
			'shipping_first_name'   => __( 'Shipping first name', 'mw-storesync-import-export' ),
			'shipping_last_name'    => __( 'Shipping last name', 'mw-storesync-import-export' ),
			'shipping_company'      => __( 'Shipping company', 'mw-storesync-import-export' ),
			'shipping_phone'        => __( 'Shipping phone', 'mw-storesync-import-export' ),
			'shipping_address_1'    => __( 'Shipping address 1', 'mw-storesync-import-export' ),
			'shipping_address_2'    => __( 'Shipping address 2', 'mw-storesync-import-export' ),
			'shipping_postcode'     => __( 'Shipping postcode', 'mw-storesync-import-export' ),
			'shipping_city'         => __( 'Shipping city', 'mw-storesync-import-export' ),
			'shipping_state'        => __( 'Shipping state', 'mw-storesync-import-export' ),
			'shipping_country'      => __( 'Shipping country', 'mw-storesync-import-export' ),
			'customer_note'         => __( 'Customer note', 'mw-storesync-import-export' ),
			'line_items'            => __( 'Line items', 'mw-storesync-import-export' ),
			'shipping_items'        => __( 'Shipping items', 'mw-storesync-import-export' ),
			'coupon_items'          => __( 'Coupon items', 'mw-storesync-import-export' ),
			'order_notes'           => __( 'Order notes', 'mw-storesync-import-export' ),
		);

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '8.5', '>=' ) ) {
			$columns['meta:_wc_order_attribution_source_type'] = __( 'WC attribution source type', 'mw-storesync-import-export' );
			$columns['meta:_wc_order_attribution_utm_source']  = __( 'WC attribution UTM source', 'mw-storesync-import-export' );
			$columns['meta:_wc_order_attribution_referrer']    = __( 'WC attribution referrer', 'mw-storesync-import-export' );
		}

		return apply_filters( 'mw_wie_order_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}


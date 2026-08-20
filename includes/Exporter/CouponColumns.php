<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CouponColumns {
	public static function get_columns() {
		return array(
			'coupon_id'                => __( 'Coupon ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'code'                     => __( 'Coupon code', 'mw-order-import-export-sync-for-woocommerce' ),
			'amount'                   => __( 'Discount amount', 'mw-order-import-export-sync-for-woocommerce' ),
			'discount_type'            => __( 'Discount type', 'mw-order-import-export-sync-for-woocommerce' ),
			'date_expires'             => __( 'Expiry date', 'mw-order-import-export-sync-for-woocommerce' ),
			'usage_count'              => __( 'Usage count', 'mw-order-import-export-sync-for-woocommerce' ),
			'usage_limit'              => __( 'Usage limit', 'mw-order-import-export-sync-for-woocommerce' ),
			'individual_use'           => __( 'Individual use only', 'mw-order-import-export-sync-for-woocommerce' ),
			'free_shipping'            => __( 'Free shipping', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_ids'              => __( 'Included products', 'mw-order-import-export-sync-for-woocommerce' ),
			'excluded_product_ids'     => __( 'Excluded products', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_categories'       => __( 'Included categories', 'mw-order-import-export-sync-for-woocommerce' ),
			'excluded_product_categories' => __( 'Excluded categories', 'mw-order-import-export-sync-for-woocommerce' ),
			'email_restrictions'       => __( 'Email restrictions', 'mw-order-import-export-sync-for-woocommerce' ),
			'minimum_amount'           => __( 'Minimum spend', 'mw-order-import-export-sync-for-woocommerce' ),
			'maximum_amount'           => __( 'Maximum spend', 'mw-order-import-export-sync-for-woocommerce' ),
			'description'              => __( 'Description', 'mw-order-import-export-sync-for-woocommerce' ),
		);
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

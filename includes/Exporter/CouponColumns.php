<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CouponColumns {
	public static function get_columns() {
		return array(
			'coupon_id'                => __( 'Coupon ID', 'mw-storesync-import-export' ),
			'code'                     => __( 'Coupon code', 'mw-storesync-import-export' ),
			'amount'                   => __( 'Discount amount', 'mw-storesync-import-export' ),
			'discount_type'            => __( 'Discount type', 'mw-storesync-import-export' ),
			'date_expires'             => __( 'Expiry date', 'mw-storesync-import-export' ),
			'usage_count'              => __( 'Usage count', 'mw-storesync-import-export' ),
			'usage_limit'              => __( 'Usage limit', 'mw-storesync-import-export' ),
			'individual_use'           => __( 'Individual use only', 'mw-storesync-import-export' ),
			'free_shipping'            => __( 'Free shipping', 'mw-storesync-import-export' ),
			'product_ids'              => __( 'Included products', 'mw-storesync-import-export' ),
			'excluded_product_ids'     => __( 'Excluded products', 'mw-storesync-import-export' ),
			'product_categories'       => __( 'Included categories', 'mw-storesync-import-export' ),
			'excluded_product_categories' => __( 'Excluded categories', 'mw-storesync-import-export' ),
			'email_restrictions'       => __( 'Email restrictions', 'mw-storesync-import-export' ),
			'minimum_amount'           => __( 'Minimum spend', 'mw-storesync-import-export' ),
			'maximum_amount'           => __( 'Maximum spend', 'mw-storesync-import-export' ),
			'description'              => __( 'Description', 'mw-storesync-import-export' ),
		);
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

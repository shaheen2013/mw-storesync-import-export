<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductReviewsColumns {
	public static function get_columns() {
		$columns = array(
			'review_id'            => __( 'Review ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_id'          => __( 'Product ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_sku'         => __( 'Product SKU', 'mw-order-import-export-sync-for-woocommerce' ),
			'product_title'       => __( 'Product Title', 'mw-order-import-export-sync-for-woocommerce' ),
			'reviewer_name'       => __( 'Reviewer Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'reviewer_email'      => __( 'Reviewer Email', 'mw-order-import-export-sync-for-woocommerce' ),
			'reviewer_ip'         => __( 'Reviewer IP', 'mw-order-import-export-sync-for-woocommerce' ),
			'review_date'         => __( 'Review Date', 'mw-order-import-export-sync-for-woocommerce' ),
			'review_content'      => __( 'Review Content', 'mw-order-import-export-sync-for-woocommerce' ),
			'review_approved'     => __( 'Approved', 'mw-order-import-export-sync-for-woocommerce' ),
			'rating'              => __( 'Rating', 'mw-order-import-export-sync-for-woocommerce' ),
			'verified'            => __( 'Verified Buyer', 'mw-order-import-export-sync-for-woocommerce' ),
			'reply_to'            => __( 'Reply To (Parent ID)', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		return apply_filters( 'mw_wie_product_reviews_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

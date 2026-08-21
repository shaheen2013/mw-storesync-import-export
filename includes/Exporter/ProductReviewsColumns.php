<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductReviewsColumns {
	public static function get_columns() {
		$columns = array(
			'review_id'            => __( 'Review ID', 'mw-storesync-import-export' ),
			'product_id'          => __( 'Product ID', 'mw-storesync-import-export' ),
			'product_sku'         => __( 'Product SKU', 'mw-storesync-import-export' ),
			'product_title'       => __( 'Product Title', 'mw-storesync-import-export' ),
			'reviewer_name'       => __( 'Reviewer Name', 'mw-storesync-import-export' ),
			'reviewer_email'      => __( 'Reviewer Email', 'mw-storesync-import-export' ),
			'reviewer_ip'         => __( 'Reviewer IP', 'mw-storesync-import-export' ),
			'review_date'         => __( 'Review Date', 'mw-storesync-import-export' ),
			'review_content'      => __( 'Review Content', 'mw-storesync-import-export' ),
			'review_approved'     => __( 'Approved', 'mw-storesync-import-export' ),
			'rating'              => __( 'Rating', 'mw-storesync-import-export' ),
			'verified'            => __( 'Verified Buyer', 'mw-storesync-import-export' ),
			'reply_to'            => __( 'Reply To (Parent ID)', 'mw-storesync-import-export' ),
		);

		return apply_filters( 'mw_wie_product_reviews_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

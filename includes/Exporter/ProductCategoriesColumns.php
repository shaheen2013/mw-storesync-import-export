<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductCategoriesColumns {
	public static function get_columns() {
		$columns = array(
			'term_id'      => __( 'Category ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'name'         => __( 'Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'slug'         => __( 'Slug', 'mw-order-import-export-sync-for-woocommerce' ),
			'description'  => __( 'Description', 'mw-order-import-export-sync-for-woocommerce' ),
			'parent_id'    => __( 'Parent Category ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'parent_slug'  => __( 'Parent Category Slug', 'mw-order-import-export-sync-for-woocommerce' ),
			'display_type' => __( 'Display Type', 'mw-order-import-export-sync-for-woocommerce' ),
			'thumbnail'    => __( 'Thumbnail Image URL', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		return apply_filters( 'mw_wie_product_categories_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

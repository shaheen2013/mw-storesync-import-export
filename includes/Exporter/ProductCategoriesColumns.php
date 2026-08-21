<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductCategoriesColumns {
	public static function get_columns() {
		$columns = array(
			'term_id'      => __( 'Category ID', 'mw-storesync-import-export' ),
			'name'         => __( 'Name', 'mw-storesync-import-export' ),
			'slug'         => __( 'Slug', 'mw-storesync-import-export' ),
			'description'  => __( 'Description', 'mw-storesync-import-export' ),
			'parent_id'    => __( 'Parent Category ID', 'mw-storesync-import-export' ),
			'parent_slug'  => __( 'Parent Category Slug', 'mw-storesync-import-export' ),
			'display_type' => __( 'Display Type', 'mw-storesync-import-export' ),
			'thumbnail'    => __( 'Thumbnail Image URL', 'mw-storesync-import-export' ),
		);

		return apply_filters( 'mw_wie_product_categories_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

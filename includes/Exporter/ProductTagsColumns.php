<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductTagsColumns {
	public static function get_columns() {
		$columns = array(
			'term_id'     => __( 'Tag ID', 'mw-storesync-import-export' ),
			'name'        => __( 'Name', 'mw-storesync-import-export' ),
			'slug'        => __( 'Slug', 'mw-storesync-import-export' ),
			'description' => __( 'Description', 'mw-storesync-import-export' ),
		);

		return apply_filters( 'mw_wie_product_tags_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

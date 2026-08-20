<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductTagsColumns {
	public static function get_columns() {
		$columns = array(
			'term_id'     => __( 'Tag ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'name'        => __( 'Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'slug'        => __( 'Slug', 'mw-order-import-export-sync-for-woocommerce' ),
			'description' => __( 'Description', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		return apply_filters( 'mw_wie_product_tags_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

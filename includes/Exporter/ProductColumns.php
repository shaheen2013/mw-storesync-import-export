<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductColumns {
	public static function get_columns() {
		$columns = array(
			'product_id'        => __( 'Product ID', 'mw-storesync-import-export' ),
			'sku'               => __( 'SKU', 'mw-storesync-import-export' ),
			'name'              => __( 'Name', 'mw-storesync-import-export' ),
			'type'              => __( 'Product type', 'mw-storesync-import-export' ),
			'status'            => __( 'Status', 'mw-storesync-import-export' ),
			'regular_price'     => __( 'Regular price', 'mw-storesync-import-export' ),
			'sale_price'        => __( 'Sale price', 'mw-storesync-import-export' ),
			'manage_stock'      => __( 'Manage stock', 'mw-storesync-import-export' ),
			'stock_quantity'    => __( 'Stock quantity', 'mw-storesync-import-export' ),
			'stock_status'      => __( 'Stock status', 'mw-storesync-import-export' ),
			'backorders'        => __( 'Backorders', 'mw-storesync-import-export' ),
			'description'       => __( 'Description', 'mw-storesync-import-export' ),
			'short_description' => __( 'Short description', 'mw-storesync-import-export' ),
			'categories'        => __( 'Categories', 'mw-storesync-import-export' ),
			'tags'              => __( 'Tags', 'mw-storesync-import-export' ),
			'images'            => __( 'Images', 'mw-storesync-import-export' ),
		);

		return apply_filters( 'mw_wie_product_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

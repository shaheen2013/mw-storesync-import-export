<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductColumns {
	public static function get_columns() {
		$columns = array(
			'product_id'        => __( 'Product ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'sku'               => __( 'SKU', 'mw-order-import-export-sync-for-woocommerce' ),
			'name'              => __( 'Name', 'mw-order-import-export-sync-for-woocommerce' ),
			'type'              => __( 'Product type', 'mw-order-import-export-sync-for-woocommerce' ),
			'status'            => __( 'Status', 'mw-order-import-export-sync-for-woocommerce' ),
			'regular_price'     => __( 'Regular price', 'mw-order-import-export-sync-for-woocommerce' ),
			'sale_price'        => __( 'Sale price', 'mw-order-import-export-sync-for-woocommerce' ),
			'manage_stock'      => __( 'Manage stock', 'mw-order-import-export-sync-for-woocommerce' ),
			'stock_quantity'    => __( 'Stock quantity', 'mw-order-import-export-sync-for-woocommerce' ),
			'stock_status'      => __( 'Stock status', 'mw-order-import-export-sync-for-woocommerce' ),
			'backorders'        => __( 'Backorders', 'mw-order-import-export-sync-for-woocommerce' ),
			'description'       => __( 'Description', 'mw-order-import-export-sync-for-woocommerce' ),
			'short_description' => __( 'Short description', 'mw-order-import-export-sync-for-woocommerce' ),
			'categories'        => __( 'Categories', 'mw-order-import-export-sync-for-woocommerce' ),
			'tags'              => __( 'Tags', 'mw-order-import-export-sync-for-woocommerce' ),
			'images'            => __( 'Images', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		return apply_filters( 'mw_wie_product_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array_keys( self::get_columns() );
	}
}

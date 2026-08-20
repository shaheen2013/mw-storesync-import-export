<?php
namespace MW\WooImportExport\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || 'toplevel_page_mw-order-import-export-sync' !== $screen->id ) {
			return;
		}

		wp_enqueue_style(
			'mw-wie-admin-style',
			MW_WIE_PLUGIN_URL . 'assets/css/admin-style.css',
			array(),
			MW_WIE_VERSION
		);

		wp_enqueue_script(
			'mw-wie-admin-script',
			MW_WIE_PLUGIN_URL . 'assets/js/admin.js',
			array( 'wp-element' ),
			MW_WIE_VERSION,
			true
		);

		wp_localize_script(
			'mw-wie-admin-script',
			'mwWieParams',
			array(
				'labels' => array(
					'order'              => __( 'Orders', 'mw-order-import-export-sync-for-woocommerce' ),
					'coupon'             => __( 'Coupons', 'mw-order-import-export-sync-for-woocommerce' ),
					'product'            => __( 'Products', 'mw-order-import-export-sync-for-woocommerce' ),
					'product_reviews'    => __( 'Product Reviews', 'mw-order-import-export-sync-for-woocommerce' ),
					'product_categories' => __( 'Product Categories', 'mw-order-import-export-sync-for-woocommerce' ),
					'product_tags'       => __( 'Product Tags', 'mw-order-import-export-sync-for-woocommerce' ),
					'subscriptions'      => __( 'Subscriptions', 'mw-order-import-export-sync-for-woocommerce' ),
					'user'               => __( 'Users', 'mw-order-import-export-sync-for-woocommerce' ),
					'reviews'            => __( 'Reviews', 'mw-order-import-export-sync-for-woocommerce' ),
					'export'             => __( 'Export', 'mw-order-import-export-sync-for-woocommerce' ),
					'import'             => __( 'Import', 'mw-order-import-export-sync-for-woocommerce' ),
					'select_method'      => __( 'Select export method — ', 'mw-order-import-export-sync-for-woocommerce' ),
					'choose_desc'        => __( 'Choose between a fast default export or a more customizable workflow for ', 'mw-order-import-export-sync-for-woocommerce' ),
				),
			)
		);
	}
}


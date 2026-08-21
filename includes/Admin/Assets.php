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

		if ( ! $screen || 'toplevel_page_mw-storesync-import-export' !== $screen->id ) {
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
					'order'              => __( 'Orders', 'mw-storesync-import-export' ),
					'coupon'             => __( 'Coupons', 'mw-storesync-import-export' ),
					'product'            => __( 'Products', 'mw-storesync-import-export' ),
					'product_reviews'    => __( 'Product Reviews', 'mw-storesync-import-export' ),
					'product_categories' => __( 'Product Categories', 'mw-storesync-import-export' ),
					'product_tags'       => __( 'Product Tags', 'mw-storesync-import-export' ),
					'subscriptions'      => __( 'Subscriptions', 'mw-storesync-import-export' ),
					'user'               => __( 'Users', 'mw-storesync-import-export' ),
					'reviews'            => __( 'Reviews', 'mw-storesync-import-export' ),
					'export'             => __( 'Export', 'mw-storesync-import-export' ),
					'import'             => __( 'Import', 'mw-storesync-import-export' ),
					'select_method'      => __( 'Select export method — ', 'mw-storesync-import-export' ),
					'choose_desc'        => __( 'Choose between a fast default export or a more customizable workflow for ', 'mw-storesync-import-export' ),
				),
			)
		);
	}
}


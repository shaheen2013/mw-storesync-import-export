<?php
namespace MW\WooImportExport\Core;

use MW\WooImportExport\Admin\Assets;
use MW\WooImportExport\Admin\Dashboard;
use MW\WooImportExport\Core\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	public function run() {
		if ( is_admin() && class_exists( 'WooCommerce' ) ) {
			new Dashboard();
			new Assets();
		}
		
		new Shortcode();

		add_filter( 'plugin_action_links_' . plugin_basename( MW_WIE_PLUGIN_FILE ), array( $this, 'add_action_links' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_woocommerce_notice' ) );
	}

	public static function activate() {
		update_option( 'mw_wie_db_version', MW_WIE_VERSION, false );
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'mw_wie_cron_schedule' );
	}

	public function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=mw-order-import-export-sync' ) ) . '">' . esc_html__( 'Import / Export', 'mw-order-import-export-sync-for-woocommerce' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	public function maybe_show_woocommerce_notice() {
		if ( class_exists( 'WooCommerce' ) ) {
			return;
		}

		$message = esc_html__( 'MW Order Import Export & Sync for WooCommerce requires WooCommerce to be active.', 'mw-order-import-export-sync-for-woocommerce' );

		echo wp_kses(
			'<div class="notice notice-error"><p>' . $message . '</p></div>',
			array(
				'div' => array(
					'class' => array(),
				),
				'p'   => array(),
			)
		);
	}
}

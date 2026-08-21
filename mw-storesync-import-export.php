<?php
/**
 * Plugin Name: StoreSync Import Export for WooCommerce
 * Plugin URI: https://mediusware.com/
 * Description: Import and export WooCommerce orders with HPOS-compatible CRUD workflows.
 * Version: 1.1.4
 * Author: Mediusware
 * Author URI: https://mediusware.com/
 * Text Domain: mw-storesync-import-export
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 7.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package MW\WooImportExport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MW_WIE_VERSION', '1.1.4' );
define( 'MW_WIE_PLUGIN_FILE', __FILE__ );
define( 'MW_WIE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MW_WIE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MW_WIE_TEXT_DOMAIN', 'mw-storesync-import-export' );

add_action( 'before_woocommerce_init', 'mw_wie_declare_hpos_compatibility' );

function mw_wie_declare_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
}

require_once MW_WIE_PLUGIN_DIR . 'includes/Core/Autoloader.php';
\MW\WooImportExport\Core\Autoloader::register();

register_activation_hook( __FILE__, array( \MW\WooImportExport\Core\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \MW\WooImportExport\Core\Plugin::class, 'deactivate' ) );

add_action( 'admin_init', 'mw_wie_check_woocommerce_dependency' );

/**
 * Check if WooCommerce is active on admin init.
 * If not, deactivate this plugin and show an error notice on the same page.
 */
function mw_wie_check_woocommerce_dependency() {
	if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && is_plugin_active( plugin_basename( MW_WIE_PLUGIN_FILE ) ) ) {
			// Deactivate the plugin
			deactivate_plugins( plugin_basename( MW_WIE_PLUGIN_FILE ) );
			
			// Remove the "Plugin activated" success message
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			
			// Show the error notice on the same page
			add_action( 'admin_notices', 'mw_wie_woocommerce_missing_notice' );
		}
	}
}

/**
 * Display the admin notice when activation fails due to missing dependency.
 */
function mw_wie_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error is-dismissible">
		<p><strong><?php esc_html_e( 'StoreSync Import Export for WooCommerce', 'mw-storesync-import-export' ); ?></strong> <?php esc_html_e( 'requires WooCommerce to be installed and active. The plugin has been deactivated.', 'mw-storesync-import-export' ); ?></p>
	</div>
	<?php
}

add_action( 'plugins_loaded', 'mw_wie_boot_plugin' );

/**
 * Bootstrap the plugin after all plugins have loaded.
 * Silently aborts if WooCommerce was deactivated after our plugin was activated.
 */
function mw_wie_boot_plugin() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		// WooCommerce was deactivated after this plugin was already active.
		// The admin notice in Plugin::maybe_show_woocommerce_notice() handles
		// user feedback; we simply do not boot any functionality.
		return;
	}

	load_plugin_textdomain( MW_WIE_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	$plugin = new \MW\WooImportExport\Core\Plugin();
	$plugin->run();
}

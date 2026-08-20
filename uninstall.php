<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

delete_option( 'mw_wie_db_version' );
delete_option( 'mw_wie_settings' );
wp_clear_scheduled_hook( 'mw_wie_cron_schedule' );

$meta_keys = array( '_mw_wie_exported', '_mw_wie_import_source' );

foreach ( $meta_keys as $meta_key ) {
	$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => $meta_key ), array( '%s' ) );

	$hpos_meta_table = $wpdb->prefix . 'wc_orders_meta';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $hpos_meta_table ) ) ) {
		$wpdb->delete( $hpos_meta_table, array( 'meta_key' => $meta_key ), array( '%s' ) );
	}
}


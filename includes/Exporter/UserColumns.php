<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UserColumns {
	public static function get_columns() {
		$columns = array(
			'ID'                    => __( 'ID', 'mw-order-import-export-sync-for-woocommerce' ),
			'customer_id'           => __( 'customer_id', 'mw-order-import-export-sync-for-woocommerce' ),
			'user_login'            => __( 'user_login', 'mw-order-import-export-sync-for-woocommerce' ),
			'user_nicename'         => __( 'user_nicename', 'mw-order-import-export-sync-for-woocommerce' ),
			'user_email'            => __( 'user_email', 'mw-order-import-export-sync-for-woocommerce' ),
			'user_url'              => __( 'user_url', 'mw-order-import-export-sync-for-woocommerce' ),
			'user_registered'       => __( 'user_registered', 'mw-order-import-export-sync-for-woocommerce' ),
			'display_name'          => __( 'display_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'first_name'            => __( 'first_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'last_name'             => __( 'last_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'user_status'           => __( 'user_status', 'mw-order-import-export-sync-for-woocommerce' ),
			'roles'                 => __( 'roles', 'mw-order-import-export-sync-for-woocommerce' ),
			'nickname'              => __( 'nickname', 'mw-order-import-export-sync-for-woocommerce' ),
			'description'           => __( 'description', 'mw-order-import-export-sync-for-woocommerce' ),
			'rich_editing'          => __( 'rich_editing', 'mw-order-import-export-sync-for-woocommerce' ),
			'syntax_highlighting'   => __( 'syntax_highlighting', 'mw-order-import-export-sync-for-woocommerce' ),
			'admin_color'           => __( 'admin_color', 'mw-order-import-export-sync-for-woocommerce' ),
			'use_ssl'               => __( 'use_ssl', 'mw-order-import-export-sync-for-woocommerce' ),
			'show_admin_bar_front'  => __( 'show_admin_bar_front', 'mw-order-import-export-sync-for-woocommerce' ),
			'locale'                => __( 'locale', 'mw-order-import-export-sync-for-woocommerce' ),
			'wp_user_level'         => __( 'wp_user_level', 'mw-order-import-export-sync-for-woocommerce' ),
			'dismissed_wp_pointers' => __( 'dismissed_wp_pointers', 'mw-order-import-export-sync-for-woocommerce' ),
			'show_welcome_panel'    => __( 'show_welcome_panel', 'mw-order-import-export-sync-for-woocommerce' ),
			'session_tokens'        => __( 'session_tokens', 'mw-order-import-export-sync-for-woocommerce' ),
			'last_update'           => __( 'last_update', 'mw-order-import-export-sync-for-woocommerce' ),
			'is_guest_user'         => __( 'is_guest_user', 'mw-order-import-export-sync-for-woocommerce' ),
			'orders'                => __( 'orders', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_first_name'    => __( 'billing_first_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_last_name'     => __( 'billing_last_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_company'       => __( 'billing_company', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_email'         => __( 'billing_email', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_phone'         => __( 'billing_phone', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_address_1'     => __( 'billing_address_1', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_address_2'     => __( 'billing_address_2', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_postcode'      => __( 'billing_postcode', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_city'          => __( 'billing_city', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_state'         => __( 'billing_state', 'mw-order-import-export-sync-for-woocommerce' ),
			'billing_country'       => __( 'billing_country', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_first_name'   => __( 'shipping_first_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_last_name'    => __( 'shipping_last_name', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_company'      => __( 'shipping_company', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_phone'        => __( 'shipping_phone', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_address_1'    => __( 'shipping_address_1', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_address_2'    => __( 'shipping_address_2', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_postcode'     => __( 'shipping_postcode', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_city'         => __( 'shipping_city', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_state'        => __( 'shipping_state', 'mw-order-import-export-sync-for-woocommerce' ),
			'shipping_country'      => __( 'shipping_country', 'mw-order-import-export-sync-for-woocommerce' ),
			'wc_last_active'        => __( 'wc_last_active', 'mw-order-import-export-sync-for-woocommerce' ),
			'total_spent'           => __( 'total_spent', 'mw-order-import-export-sync-for-woocommerce' ),
			'aov'                   => __( 'aov', 'mw-order-import-export-sync-for-woocommerce' ),
		);

		return apply_filters( 'mw_wie_user_columns', $columns );
	}

	public static function get_default_export_columns() {
		return array(
			'ID',
			'user_login',
			'user_email',
			'first_name',
			'last_name',
			'roles',
			'user_registered',
		);
	}
}

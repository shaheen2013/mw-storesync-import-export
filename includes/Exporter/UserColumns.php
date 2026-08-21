<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UserColumns {
	public static function get_columns() {
		$columns = array(
			'ID'                    => __( 'ID', 'mw-storesync-import-export' ),
			'customer_id'           => __( 'customer_id', 'mw-storesync-import-export' ),
			'user_login'            => __( 'user_login', 'mw-storesync-import-export' ),
			'user_nicename'         => __( 'user_nicename', 'mw-storesync-import-export' ),
			'user_email'            => __( 'user_email', 'mw-storesync-import-export' ),
			'user_url'              => __( 'user_url', 'mw-storesync-import-export' ),
			'user_registered'       => __( 'user_registered', 'mw-storesync-import-export' ),
			'display_name'          => __( 'display_name', 'mw-storesync-import-export' ),
			'first_name'            => __( 'first_name', 'mw-storesync-import-export' ),
			'last_name'             => __( 'last_name', 'mw-storesync-import-export' ),
			'user_status'           => __( 'user_status', 'mw-storesync-import-export' ),
			'roles'                 => __( 'roles', 'mw-storesync-import-export' ),
			'nickname'              => __( 'nickname', 'mw-storesync-import-export' ),
			'description'           => __( 'description', 'mw-storesync-import-export' ),
			'rich_editing'          => __( 'rich_editing', 'mw-storesync-import-export' ),
			'syntax_highlighting'   => __( 'syntax_highlighting', 'mw-storesync-import-export' ),
			'admin_color'           => __( 'admin_color', 'mw-storesync-import-export' ),
			'use_ssl'               => __( 'use_ssl', 'mw-storesync-import-export' ),
			'show_admin_bar_front'  => __( 'show_admin_bar_front', 'mw-storesync-import-export' ),
			'locale'                => __( 'locale', 'mw-storesync-import-export' ),
			'wp_user_level'         => __( 'wp_user_level', 'mw-storesync-import-export' ),
			'dismissed_wp_pointers' => __( 'dismissed_wp_pointers', 'mw-storesync-import-export' ),
			'show_welcome_panel'    => __( 'show_welcome_panel', 'mw-storesync-import-export' ),
			'session_tokens'        => __( 'session_tokens', 'mw-storesync-import-export' ),
			'last_update'           => __( 'last_update', 'mw-storesync-import-export' ),
			'is_guest_user'         => __( 'is_guest_user', 'mw-storesync-import-export' ),
			'orders'                => __( 'orders', 'mw-storesync-import-export' ),
			'billing_first_name'    => __( 'billing_first_name', 'mw-storesync-import-export' ),
			'billing_last_name'     => __( 'billing_last_name', 'mw-storesync-import-export' ),
			'billing_company'       => __( 'billing_company', 'mw-storesync-import-export' ),
			'billing_email'         => __( 'billing_email', 'mw-storesync-import-export' ),
			'billing_phone'         => __( 'billing_phone', 'mw-storesync-import-export' ),
			'billing_address_1'     => __( 'billing_address_1', 'mw-storesync-import-export' ),
			'billing_address_2'     => __( 'billing_address_2', 'mw-storesync-import-export' ),
			'billing_postcode'      => __( 'billing_postcode', 'mw-storesync-import-export' ),
			'billing_city'          => __( 'billing_city', 'mw-storesync-import-export' ),
			'billing_state'         => __( 'billing_state', 'mw-storesync-import-export' ),
			'billing_country'       => __( 'billing_country', 'mw-storesync-import-export' ),
			'shipping_first_name'   => __( 'shipping_first_name', 'mw-storesync-import-export' ),
			'shipping_last_name'    => __( 'shipping_last_name', 'mw-storesync-import-export' ),
			'shipping_company'      => __( 'shipping_company', 'mw-storesync-import-export' ),
			'shipping_phone'        => __( 'shipping_phone', 'mw-storesync-import-export' ),
			'shipping_address_1'    => __( 'shipping_address_1', 'mw-storesync-import-export' ),
			'shipping_address_2'    => __( 'shipping_address_2', 'mw-storesync-import-export' ),
			'shipping_postcode'     => __( 'shipping_postcode', 'mw-storesync-import-export' ),
			'shipping_city'         => __( 'shipping_city', 'mw-storesync-import-export' ),
			'shipping_state'        => __( 'shipping_state', 'mw-storesync-import-export' ),
			'shipping_country'      => __( 'shipping_country', 'mw-storesync-import-export' ),
			'wc_last_active'        => __( 'wc_last_active', 'mw-storesync-import-export' ),
			'total_spent'           => __( 'total_spent', 'mw-storesync-import-export' ),
			'aov'                   => __( 'aov', 'mw-storesync-import-export' ),
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

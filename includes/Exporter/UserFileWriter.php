<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UserFileWriter {
	public function download_users_csv( array $columns, array $filters ) {
		$available_columns = UserColumns::get_columns();
		$columns           = array_values( array_intersect( $columns, array_keys( $available_columns ) ) );

		if ( empty( $columns ) ) {
			$columns = UserColumns::get_default_export_columns();
		}

		$limit     = isset( $filters['limit'] ) ? absint( $filters['limit'] ) : 500;
		$limit     = min( max( $limit, 1 ), 5000 );
		$offset    = isset( $filters['skip'] ) ? absint( $filters['skip'] ) : 0;
		$delimiter = CsvValueSanitizer::validate_delimiter( isset( $filters['delimiter'] ) ? $filters['delimiter'] : ',' );

		$custom_name = isset( $filters['export_filename'] ) ? trim( $filters['export_filename'] ) : '';
		$file_name   = ( '' !== $custom_name ) ? sanitize_file_name( $custom_name ) . '.csv' : 'mw-users-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

		$custom_names = isset( $filters['column_names'] ) ? $filters['column_names'] : array();
		$headers      = array();
		foreach ( $columns as $column ) {
			$headers[] = isset( $custom_names[ $column ] ) && '' !== trim( $custom_names[ $column ] ) ? $custom_names[ $column ] : $column;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct output stream required for CSV download.
		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, CsvValueSanitizer::sanitize_row( $headers ), $delimiter );

		$args = array(
			'number' => $limit,
			'offset' => $offset,
		);

		if ( ! empty( $filters['user_roles'] ) ) {
			$args['role__in'] = array_map( 'trim', explode( ',', $filters['user_roles'] ) );
		}

		$users = get_users( $args );

		foreach ( $users as $user ) {
			$row = array();

			foreach ( $columns as $column ) {
				$row[] = CsvValueSanitizer::sanitize_value( $this->get_column_value( $user, $column ) );
			}

			fputcsv( $output, CsvValueSanitizer::sanitize_row( $row ), $delimiter );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct output stream close.
		fclose( $output );
		exit;
	}

	private function get_column_value( $user, $column ) {
		switch ( $column ) {
			case 'ID':
			case 'customer_id':
				return $user->ID;
			case 'user_login':
				return $user->user_login;
			case 'user_nicename':
				return $user->user_nicename;
			case 'user_email':
				return $user->user_email;
			case 'user_url':
				return $user->user_url;
			case 'user_registered':
				return $user->user_registered;
			case 'display_name':
				return $user->display_name;
			case 'user_status':
				return $user->user_status;
			case 'roles':
				return implode( ', ', $user->roles );
			case 'orders':
				return function_exists( 'wc_get_customer_order_count' ) ? wc_get_customer_order_count( $user->ID ) : 0;
			case 'total_spent':
				return function_exists( 'wc_get_customer_total_spent' ) ? wc_get_customer_total_spent( $user->ID ) : 0;
			case 'aov':
				if ( function_exists( 'wc_get_customer_order_count' ) && function_exists( 'wc_get_customer_total_spent' ) ) {
					$count = wc_get_customer_order_count( $user->ID );
					$spent = wc_get_customer_total_spent( $user->ID );
					return $count > 0 ? round( $spent / $count, 2 ) : 0;
				}
				return 0;
			case 'session_tokens':
			case 'dismissed_wp_pointers':
				// Arrays, so serialize or JSON encode
				$meta = get_user_meta( $user->ID, $column, true );
				return is_array( $meta ) ? wp_json_encode( $meta ) : $meta;
			default:
				// E.g. first_name, last_name, nickname, description, rich_editing, billing_*, shipping_*, wc_last_active, etc.
				return get_user_meta( $user->ID, $column, true );
		}
	}
}

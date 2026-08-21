<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CouponFileWriter {
	public function download_coupons_csv( array $columns, array $filters ) {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			wp_die( esc_html__( 'WooCommerce is required for coupon export.', 'mw-storesync-import-export' ) );
		}

		$available_columns = CouponColumns::get_columns();
		$columns           = array_values( array_intersect( $columns, array_keys( $available_columns ) ) );

		if ( empty( $columns ) ) {
			$columns = CouponColumns::get_default_export_columns();
		}

		$limit      = isset( $filters['limit'] ) ? absint( $filters['limit'] ) : 500;
		$limit      = min( max( $limit, 1 ), 5000 );
		$offset     = isset( $filters['skip'] ) ? absint( $filters['skip'] ) : 0;
		$delimiter  = CsvValueSanitizer::validate_delimiter( isset( $filters['delimiter'] ) ? $filters['delimiter'] : ',' );

		$custom_name = isset( $filters['export_filename'] ) ? trim( $filters['export_filename'] ) : '';
		$file_name   = ( '' !== $custom_name ) ? sanitize_file_name( $custom_name ) . '.csv' : 'mw-coupon-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

		$custom_names = isset( $filters['column_names'] ) ? $filters['column_names'] : array();
		$headers      = array();
		foreach ( $columns as $column ) {
			$headers[] = isset( $custom_names[ $column ] ) && '' !== trim( $custom_names[ $column ] ) ? $custom_names[ $column ] : $column;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct output stream required for CSV download header and rows.
		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, CsvValueSanitizer::sanitize_row( $headers ), $delimiter );

		$args = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => array( 'publish', 'draft', 'trash', 'pending' ),
			'posts_per_page' => $limit,
			'offset'         => $offset,
		);

		$query = new \WP_Query( $args );

		foreach ( $query->posts as $coupon_post ) {
			$coupon = new \WC_Coupon( $coupon_post->ID );
			$row    = array();

			foreach ( $columns as $column ) {
				$row[] = CsvValueSanitizer::sanitize_value( $this->get_column_value( $coupon, $column ) );
			}

			fputcsv( $output, CsvValueSanitizer::sanitize_row( $row ), $delimiter );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct output stream close.
		fclose( $output );
		exit;
	}

	private function get_column_value( $coupon, $column ) {
		switch ( $column ) {
			case 'coupon_id':
				return $coupon->get_id();
			case 'code':
				return $coupon->get_code();
			case 'amount':
				return $coupon->get_amount();
			case 'discount_type':
				return $coupon->get_discount_type();
			case 'date_expires':
				return $coupon->get_date_expires() ? $coupon->get_date_expires()->date( 'Y-m-d H:i:s' ) : '';
			case 'usage_count':
				return $coupon->get_usage_count();
			case 'usage_limit':
				return $coupon->get_usage_limit();
			case 'individual_use':
				return $coupon->get_individual_use() ? 'yes' : 'no';
			case 'free_shipping':
				return $coupon->get_free_shipping() ? 'yes' : 'no';
			case 'product_ids':
				return implode( ',', $coupon->get_product_ids() );
			case 'excluded_product_ids':
				return implode( ',', $coupon->get_excluded_product_ids() );
			case 'product_categories':
				return implode( ',', $coupon->get_product_categories() );
			case 'excluded_product_categories':
				return implode( ',', $coupon->get_excluded_product_categories() );
			case 'email_restrictions':
				return implode( ',', $coupon->get_email_restrictions() );
			case 'minimum_amount':
				return $coupon->get_minimum_amount();
			case 'maximum_amount':
				return $coupon->get_maximum_amount();
			case 'description':
				return $coupon->get_description();
			default:
				return '';
		}
	}
}

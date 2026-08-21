<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductFileWriter {
	public function download_products_csv( array $columns, array $filters ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_die( esc_html__( 'WooCommerce is required for product export.', 'mw-storesync-import-export' ) );
		}

		$available_columns = ProductColumns::get_columns();
		$columns           = array_values( array_intersect( $columns, array_keys( $available_columns ) ) );

		if ( empty( $columns ) ) {
			$columns = ProductColumns::get_default_export_columns();
		}

		$limit      = isset( $filters['limit'] ) ? absint( $filters['limit'] ) : 500;
		$limit      = min( max( $limit, 1 ), 5000 );
		$offset     = isset( $filters['skip'] ) ? absint( $filters['skip'] ) : 0;
		$delimiter  = CsvValueSanitizer::validate_delimiter( isset( $filters['delimiter'] ) ? $filters['delimiter'] : ',' );
		$batch_size = isset( $filters['export_batch_size'] ) ? max( 1, absint( $filters['export_batch_size'] ) ) : 30;
		$batch_size = min( $batch_size, $limit );

		$custom_name = isset( $filters['export_filename'] ) ? trim( $filters['export_filename'] ) : '';
		$file_name   = ( '' !== $custom_name ) ? sanitize_file_name( $custom_name ) . '.csv' : 'mw-product-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

		$custom_names = isset( $filters['column_names'] ) ? $filters['column_names'] : array();
		$headers      = array();
		foreach ( $columns as $column ) {
			$headers[] = isset( $custom_names[ $column ] ) && '' !== trim( $custom_names[ $column ] ) ? $custom_names[ $column ] : $column;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct output stream required for CSV download.
		$output   = fopen( 'php://output', 'w' );
		fputcsv( $output, CsvValueSanitizer::sanitize_row( $headers ), $delimiter );

		$query    = new ProductQueryBuilder();
		$exported = 0;

		while ( $exported < $limit ) {
			$products = $query->get_next_batch( $offset, min( $batch_size, $limit - $exported ), $filters );

			if ( empty( $products ) ) {
				break;
			}

			foreach ( $products as $product ) {
				$row = array();

				foreach ( $columns as $column ) {
					$row[] = CsvValueSanitizer::sanitize_value( $this->get_column_value( $product, $column ) );
				}

				fputcsv( $output, CsvValueSanitizer::sanitize_row( $row ), $delimiter );
				$product->update_meta_data( '_mw_wie_exported', current_time( 'mysql', true ) );
				$product->save();
				++$exported;
			}

			$offset += count( $products );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct output stream close.
		fclose( $output );
		exit;
	}

	private function get_column_value( $product, $column ) {
		if ( 0 === strpos( $column, 'meta:' ) ) {
			return $this->stringify( $product->get_meta( substr( $column, 5 ) ) );
		}

		switch ( $column ) {
			case 'product_id':
				return $product->get_id();
			case 'sku':
				return $product->get_sku();
			case 'name':
				return $product->get_name();
			case 'type':
				return $product->get_type();
			case 'status':
				return $product->get_status();
			case 'regular_price':
				return $product->get_regular_price();
			case 'sale_price':
				return $product->get_sale_price();
			case 'manage_stock':
				return $product->get_manage_stock() ? 'yes' : 'no';
			case 'stock_quantity':
				return $product->get_stock_quantity();
			case 'stock_status':
				return $product->get_stock_status();
			case 'backorders':
				return $product->get_backorders();
			case 'description':
				return $product->get_description();
			case 'short_description':
				return $product->get_short_description();
			case 'categories':
				return $this->get_term_names( $product->get_id(), 'product_cat' );
			case 'tags':
				return $this->get_term_names( $product->get_id(), 'product_tag' );
			case 'images':
				return $this->get_image_urls( $product );
			default:
				return '';
		}
	}

	private function get_term_names( $product_id, $taxonomy ) {
		$terms = wp_get_post_terms( $product_id, $taxonomy, array( 'fields' => 'names' ) );

		if ( is_wp_error( $terms ) ) {
			return '';
		}

		return implode( ',', $terms );
	}

	private function get_image_urls( $product ) {
		$image_urls = array();

		if ( $product->get_image_id() ) {
			$image_urls[] = wp_get_attachment_url( $product->get_image_id() );
		}

		foreach ( $product->get_gallery_image_ids() as $image_id ) {
			$image_urls[] = wp_get_attachment_url( $image_id );
		}

		return implode( ',', array_filter( $image_urls ) );
	}

	private function stringify( $value ) {
		if ( is_scalar( $value ) || null === $value ) {
			return (string) $value;
		}

		return wp_json_encode( $value );
	}
}

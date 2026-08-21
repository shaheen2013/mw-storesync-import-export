<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductCategoriesFileWriter {
	public function download_categories_csv( array $columns, array $filters ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_die( esc_html__( 'WooCommerce is required for category export.', 'mw-storesync-import-export' ) );
		}

		$available_columns = ProductCategoriesColumns::get_columns();
		$columns           = array_values( array_intersect( $columns, array_keys( $available_columns ) ) );

		if ( empty( $columns ) ) {
			$columns = ProductCategoriesColumns::get_default_export_columns();
		}

		$limit     = isset( $filters['limit'] ) ? absint( $filters['limit'] ) : 500;
		$limit     = min( max( $limit, 1 ), 5000 );
		$offset    = isset( $filters['skip'] ) ? absint( $filters['skip'] ) : 0;
		$delimiter = CsvValueSanitizer::validate_delimiter( isset( $filters['delimiter'] ) ? $filters['delimiter'] : ',' );

		$custom_name = isset( $filters['export_filename'] ) ? trim( $filters['export_filename'] ) : '';
		$file_name   = ( '' !== $custom_name ) ? sanitize_file_name( $custom_name ) . '.csv' : 'mw-categories-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

		$custom_names = isset( $filters['column_names'] ) ? $filters['column_names'] : array();
		$headers      = array();
		foreach ( $columns as $column ) {
			$headers[] = isset( $custom_names[ $column ] ) && '' !== trim( $custom_names[ $column ] ) ? $custom_names[ $column ] : $column;
		}

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, CsvValueSanitizer::sanitize_row( $headers ), $delimiter );

		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => $limit,
			'offset'     => $offset,
		);

		$terms = get_terms( $args );

		if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$row = array();

				foreach ( $columns as $column ) {
					$row[] = CsvValueSanitizer::sanitize_value( $this->get_column_value( $term, $column ) );
				}

				fputcsv( $output, CsvValueSanitizer::sanitize_row( $row ), $delimiter );
			}
		}

		fclose( $output );
		exit;
	}

	private function get_column_value( $term, $column ) {
		switch ( $column ) {
			case 'term_id':
				return $term->term_id;
			case 'name':
				return htmlspecialchars_decode( $term->name );
			case 'slug':
				return rawurldecode( $term->slug );
			case 'description':
				return $term->description;
			case 'parent_id':
				return $term->parent;
			case 'parent_slug':
				if ( ! empty( $term->parent ) ) {
					$parent_term = get_term( (int) $term->parent, 'product_cat' );
					if ( $parent_term && ! is_wp_error( $parent_term ) ) {
						return rawurldecode( $parent_term->slug );
					}
				}
				return '';
			case 'display_type':
				$display_type = get_term_meta( $term->term_id, 'display_type', true );
				return $display_type ? $display_type : 'default';
			case 'thumbnail':
				$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
				$thumbnail    = wp_get_attachment_url( $thumbnail_id );
				return $thumbnail ? $thumbnail : '';
			default:
				return '';
		}
	}
}

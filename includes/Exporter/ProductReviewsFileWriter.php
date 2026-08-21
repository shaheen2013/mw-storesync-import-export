<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductReviewsFileWriter {
	public function download_reviews_csv( array $columns, array $filters ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_die( esc_html__( 'WooCommerce is required for review export.', 'mw-storesync-import-export' ) );
		}

		$available_columns = ProductReviewsColumns::get_columns();
		$columns           = array_values( array_intersect( $columns, array_keys( $available_columns ) ) );

		if ( empty( $columns ) ) {
			$columns = ProductReviewsColumns::get_default_export_columns();
		}

		$limit     = isset( $filters['limit'] ) ? absint( $filters['limit'] ) : 500;
		$limit     = min( max( $limit, 1 ), 5000 );
		$offset    = isset( $filters['skip'] ) ? absint( $filters['skip'] ) : 0;
		$delimiter = CsvValueSanitizer::validate_delimiter( isset( $filters['delimiter'] ) ? $filters['delimiter'] : ',' );

		$custom_name = isset( $filters['export_filename'] ) ? trim( $filters['export_filename'] ) : '';
		$file_name   = ( '' !== $custom_name ) ? sanitize_file_name( $custom_name ) . '.csv' : 'mw-reviews-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

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
			'post_type' => 'product',
			'orderby'   => 'comment_ID',
			'order'     => 'ASC',
			'number'    => $limit,
			'offset'    => $offset,
		);

		if ( isset( $filters['status'] ) && '' !== $filters['status'] ) {
			$args['status'] = sanitize_key( $filters['status'] );
		}

		if ( ! empty( $filters['stars'] ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'rating',
					'value' => absint( $filters['stars'] ),
				),
			);
		}

		if ( ! empty( $filters['product'] ) ) {
			$product_val = sanitize_text_field( $filters['product'] );
			if ( is_numeric( $product_val ) ) {
				$args['post_id'] = absint( $product_val );
			} else {
				$prod_id = wc_get_product_id_by_sku( $product_val );
				if ( $prod_id ) {
					$args['post_id'] = $prod_id;
				} else {
					$args['post_id'] = -1;
				}
			}
		}

		if ( ! empty( $filters['date_from'] ) || ! empty( $filters['date_to'] ) ) {
			$args['date_query'] = array();
			if ( ! empty( $filters['date_from'] ) ) {
				$args['date_query'][] = array(
					'after'     => sanitize_text_field( $filters['date_from'] ),
					'inclusive' => true,
				);
			}
			if ( ! empty( $filters['date_to'] ) ) {
				$args['date_query'][] = array(
					'before'    => sanitize_text_field( $filters['date_to'] ),
					'inclusive' => true,
				);
			}
		}

		$query    = new \WP_Comment_Query();
		$comments = $query->query( $args );

		if ( is_array( $comments ) ) {
			foreach ( $comments as $comment ) {
				$row = array();

				foreach ( $columns as $column ) {
					$row[] = CsvValueSanitizer::sanitize_value( $this->get_column_value( $comment, $column ) );
				}

				fputcsv( $output, CsvValueSanitizer::sanitize_row( $row ), $delimiter );
			}
		}

		fclose( $output );
		exit;
	}

	private function get_column_value( $comment, $column ) {
		switch ( $column ) {
			case 'review_id':
				return $comment->comment_ID;
			case 'product_id':
				return $comment->comment_post_ID;
			case 'product_sku':
				return get_post_meta( $comment->comment_post_ID, '_sku', true );
			case 'product_title':
				return get_the_title( $comment->comment_post_ID );
			case 'reviewer_name':
				return $comment->comment_author;
			case 'reviewer_email':
				return $comment->comment_author_email;
			case 'reviewer_ip':
				return $comment->comment_author_IP;
			case 'review_date':
				return $comment->comment_date;
			case 'review_content':
				return $comment->comment_content;
			case 'review_approved':
				return $comment->comment_approved;
			case 'rating':
				return get_comment_meta( $comment->comment_ID, 'rating', true );
			case 'verified':
				return get_comment_meta( $comment->comment_ID, 'verified', true ) ? 'yes' : 'no';
			case 'reply_to':
				return $comment->comment_parent;
			default:
				return '';
		}
	}
}

<?php
namespace MW\WooImportExport\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductReviewsProvisioner {
	public function import_rows( array $rows ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array( 'error' => __( 'WooCommerce is required for review import.', 'mw-storesync-import-export' ) );
		}

		$result = array(
			'success' => 0,
			'failed'  => 0,
			'logs'    => array(),
		);

		foreach ( $rows as $index => $row ) {
			$row_number = $index + 2;
			$response   = $this->import_row( $row );

			if ( is_wp_error( $response ) ) {
				++$result['failed'];
				$result['logs'][] = sprintf(
					/* translators: 1: row number, 2: error message */
					__( 'Row %1$d failed: %2$s', 'mw-storesync-import-export' ),
					$row_number,
					$response->get_error_message()
				);
				continue;
			}

			++$result['success'];
			$result['logs'][] = sprintf(
				/* translators: 1: row number, 2: review ID */
				__( 'Row %1$d imported as review #%2$d.', 'mw-storesync-import-export' ),
				$row_number,
				$response
			);
		}

		return $result;
	}

	private function import_row( array $row ) {
		$product_id = ! empty( $row['product_id'] ) ? absint( $row['product_id'] ) : 0;
		if ( ! $product_id && ! empty( $row['product_sku'] ) ) {
			$product_id = wc_get_product_id_by_sku( sanitize_text_field( $row['product_sku'] ) );
		}

		if ( ! $product_id ) {
			return new \WP_Error( 'mw_wie_review_import_error', __( 'Missing product ID or SKU.', 'mw-storesync-import-export' ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return new \WP_Error( 'mw_wie_review_import_error', __( 'Product not found.', 'mw-storesync-import-export' ) );
		}

		$review_id = ! empty( $row['review_id'] ) ? absint( $row['review_id'] ) : 0;
		$comment   = $review_id ? get_comment( $review_id ) : false;

		$commentdata = array(
			'comment_post_ID'      => $product_id,
			'comment_author'       => sanitize_text_field( $row['reviewer_name'] ?? '' ),
			'comment_author_email' => sanitize_email( $row['reviewer_email'] ?? '' ),
			'comment_author_IP'    => sanitize_text_field( $row['reviewer_ip'] ?? '' ),
			'comment_date'         => sanitize_text_field( $row['review_date'] ?? '' ),
			'comment_content'      => wp_kses_post( $row['review_content'] ?? '' ),
			'comment_approved'     => isset( $row['review_approved'] ) ? sanitize_text_field( $row['review_approved'] ) : '1',
			'comment_parent'       => absint( $row['reply_to'] ?? 0 ),
			'user_id'              => absint( $row['customer_id'] ?? 0 ),
			'comment_type'         => 'review',
		);

		if ( $comment && $comment->comment_type === 'review' ) {
			$commentdata['comment_ID'] = $comment->comment_ID;
			wp_update_comment( $commentdata );
			$inserted_id = $comment->comment_ID;
		} else {
			$inserted_id = wp_insert_comment( $commentdata );
		}

		if ( ! $inserted_id || is_wp_error( $inserted_id ) ) {
			return new \WP_Error( 'mw_wie_review_import_error', __( 'Failed to save review.', 'mw-storesync-import-export' ) );
		}

		if ( isset( $row['rating'] ) ) {
			update_comment_meta( $inserted_id, 'rating', absint( $row['rating'] ) );
		}

		if ( isset( $row['verified'] ) ) {
			$verified_val = in_array( strtolower( $row['verified'] ), array( '1', 'yes', 'true', 'on' ), true ) ? 1 : 0;
			update_comment_meta( $inserted_id, 'verified', $verified_val );
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients( $product_id );
		}

		wp_update_comment_count( $product_id );

		return $inserted_id;
	}
}

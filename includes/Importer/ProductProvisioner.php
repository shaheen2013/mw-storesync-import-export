<?php
namespace MW\WooImportExport\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductProvisioner {
	public function import_rows( array $rows ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array( 'error' => __( 'WooCommerce is required for product import.', 'mw-order-import-export-sync-for-woocommerce' ) );
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
					__( 'Row %1$d failed: %2$s', 'mw-order-import-export-sync-for-woocommerce' ),
					$row_number,
					$response->get_error_message()
				);
				continue;
			}

			++$result['success'];
			$result['logs'][] = sprintf(
				/* translators: 1: row number, 2: product ID */
				__( 'Row %1$d imported as product #%2$d.', 'mw-order-import-export-sync-for-woocommerce' ),
				$row_number,
				$response
			);
		}

		return $result;
	}

	private function import_row( array $row ) {
		$product_id = ! empty( $row['product_id'] ) ? absint( $row['product_id'] ) : 0;
		$product     = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product && ! empty( $row['sku'] ) ) {
			$existing_id = wc_get_product_id_by_sku( sanitize_text_field( $row['sku'] ) );
			if ( $existing_id ) {
				$product = wc_get_product( $existing_id );
			}
		}

		if ( ! $product ) {
			$product_id = $this->create_product( $row );
			if ( is_wp_error( $product_id ) ) {
				return $product_id;
			}

			$product = wc_get_product( $product_id );
		}

		if ( ! $product ) {
			return new \WP_Error( 'mw_wie_product_import_error', __( 'Unable to create or retrieve the product.', 'mw-order-import-export-sync-for-woocommerce' ) );
		}

		$this->apply_core_fields( $product, $row );
		$this->apply_stock_fields( $product, $row );
		$this->apply_taxonomy_fields( $product, $row );
		$this->apply_meta( $product, $row );

		$product->save();

		return $product->get_id();
	}

	private function create_product( array $row ) {
		$post_title   = ! empty( $row['name'] ) ? sanitize_text_field( $row['name'] ) : __( 'Untitled product', 'mw-order-import-export-sync-for-woocommerce' );
		$post_content = ! empty( $row['description'] ) ? wp_kses_post( $row['description'] ) : '';
		$post_excerpt = ! empty( $row['short_description'] ) ? wp_kses_post( $row['short_description'] ) : '';
		$post_status  = ! empty( $row['status'] ) ? sanitize_key( $row['status'] ) : 'publish';

		$product_id = wp_insert_post(
			array(
				'post_title'   => $post_title,
				'post_content' => $post_content,
				'post_excerpt' => $post_excerpt,
				'post_status'  => $post_status,
				'post_type'    => 'product',
			)
		);

		if ( is_wp_error( $product_id ) || ! $product_id ) {
			return new \WP_Error( 'mw_wie_product_create_error', __( 'Unable to create product post.', 'mw-order-import-export-sync-for-woocommerce' ) );
		}

		return $product_id;
	}

	private function apply_core_fields( $product, array $row ) {
		if ( ! empty( $row['name'] ) ) {
			$product->set_name( sanitize_text_field( $row['name'] ) );
		}

		if ( ! empty( $row['status'] ) ) {
			$product->set_status( sanitize_key( $row['status'] ) );
		}

		if ( ! empty( $row['sku'] ) ) {
			$product->set_sku( sanitize_text_field( $row['sku'] ) );
		}

		if ( ! empty( $row['regular_price'] ) ) {
			$product->set_regular_price( sanitize_text_field( $row['regular_price'] ) );
		}

		if ( ! empty( $row['sale_price'] ) ) {
			$product->set_sale_price( sanitize_text_field( $row['sale_price'] ) );
		}

		if ( isset( $row['description'] ) ) {
			$product->set_description( wp_kses_post( $row['description'] ) );
		}

		if ( isset( $row['short_description'] ) ) {
			$product->set_short_description( wp_kses_post( $row['short_description'] ) );
		}

		if ( ! empty( $row['type'] ) ) {
			$type = sanitize_key( $row['type'] );
			if ( in_array( $type, array( 'simple', 'grouped', 'external', 'variable' ), true ) ) {
				if ( method_exists( $product, 'set_type' ) ) {
					$product->set_type( $type );
				}
				wp_set_object_terms( $product->get_id(), $type, 'product_type' );
			}
		}
	}

	private function apply_stock_fields( $product, array $row ) {
		if ( isset( $row['manage_stock'] ) ) {
			$manage_stock = sanitize_text_field( $row['manage_stock'] );
			$product->set_manage_stock( in_array( strtolower( $manage_stock ), array( '1', 'yes', 'true', 'on' ), true ) );
		}

		if ( isset( $row['stock_quantity'] ) ) {
			$product->set_stock_quantity( absint( $row['stock_quantity'] ) );
		}

		if ( ! empty( $row['stock_status'] ) ) {
			$product->set_stock_status( sanitize_key( $row['stock_status'] ) );
		}

		if ( isset( $row['backorders'] ) ) {
			$product->set_backorders( sanitize_text_field( $row['backorders'] ) );
		}
	}

	private function apply_taxonomy_fields( $product, array $row ) {
		if ( ! empty( $row['categories'] ) ) {
			$categories = $this->normalize_comma_list( $row['categories'] );
			wp_set_object_terms( $product->get_id(), $categories, 'product_cat' );
		}

		if ( ! empty( $row['tags'] ) ) {
			$tags = $this->normalize_comma_list( $row['tags'] );
			wp_set_object_terms( $product->get_id(), $tags, 'product_tag' );
		}

		if ( ! empty( $row['images'] ) ) {
			$image_ids = $this->resolve_image_ids( $row['images'] );
			if ( ! empty( $image_ids ) ) {
				$product->set_image_id( $image_ids[0] );
				if ( count( $image_ids ) > 1 ) {
					$product->set_gallery_image_ids( array_slice( $image_ids, 1 ) );
				}
			}
		}
	}

	private function apply_meta( $product, array $row ) {
		foreach ( $row as $key => $value ) {
			if ( 0 !== strpos( $key, 'meta:' ) || '' === $value ) {
				continue;
			}

			$product->update_meta_data( substr( $key, 5 ), sanitize_text_field( $value ) );
		}
	}

	private function normalize_comma_list( $value ) {
		$values = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		return array_map( 'sanitize_text_field', $values );
	}

	private function resolve_image_ids( $value ) {
		$image_ids = array();

		foreach ( $this->normalize_comma_list( $value ) as $image_value ) {
			if ( ctype_digit( $image_value ) ) {
				$image_id = absint( $image_value );
			} else {
				$image_id = function_exists( 'attachment_url_to_postid' ) ? attachment_url_to_postid( esc_url_raw( $image_value ) ) : 0;
			}

			if ( $image_id ) {
				$image_ids[] = $image_id;
			}
		}

		return array_values( array_unique( $image_ids ) );
	}
}

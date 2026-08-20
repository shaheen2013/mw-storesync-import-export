<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductQueryBuilder {
	public function get_next_batch( $offset = 0, $limit = 100, array $filters = array() ) {
		$args = array(
			'limit'   => $limit,
			'offset'  => absint( $offset ),
			'orderby' => 'id',
			'order'   => 'ASC',
			'return'  => 'objects',
		);

		if ( ! empty( $filters['product_ids'] ) ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', $filters['product_ids'] ) ) );
			if ( ! empty( $product_ids ) ) {
				$args['include'] = $product_ids;
			}
		}

		if ( ! empty( $filters['sku'] ) ) {
			$args['sku'] = sanitize_text_field( $filters['sku'] );
		}

		if ( ! empty( $filters['type'] ) ) {
			$args['type'] = sanitize_key( $filters['type'] );
		}

		if ( ! empty( $filters['status'] ) ) {
			$args['status'] = sanitize_key( $filters['status'] );
		}

		if ( ! empty( $filters['search'] ) ) {
			$args['search'] = sanitize_text_field( $filters['search'] );
		}

		return wc_get_products( $args );
	}
}

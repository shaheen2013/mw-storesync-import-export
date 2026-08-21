<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SubscriptionsFileWriter {
	public function download_subscriptions_csv( array $columns, array $filters ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_die( esc_html__( 'WooCommerce is required for subscription export.', 'mw-storesync-import-export' ) );
		}

		$available_columns = SubscriptionsColumns::get_columns();
		$columns           = array_values( array_intersect( $columns, array_keys( $available_columns ) ) );

		if ( empty( $columns ) ) {
			$columns = SubscriptionsColumns::get_default_export_columns();
		}

		$limit     = isset( $filters['limit'] ) ? absint( $filters['limit'] ) : 500;
		$limit     = min( max( $limit, 1 ), 5000 );
		$offset    = isset( $filters['skip'] ) ? absint( $filters['skip'] ) : 0;
		$delimiter = CsvValueSanitizer::validate_delimiter( isset( $filters['delimiter'] ) ? $filters['delimiter'] : ',' );

		$custom_name = isset( $filters['export_filename'] ) ? trim( $filters['export_filename'] ) : '';
		$file_name   = ( '' !== $custom_name ) ? sanitize_file_name( $custom_name ) . '.csv' : 'mw-subscriptions-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

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
			'limit'   => $limit,
			'offset'  => $offset,
			'orderby' => 'id',
			'order'   => 'ASC',
			'type'    => 'shop_subscription',
			'return'  => 'objects',
		);

		if ( ! empty( $filters['status'] ) ) {
			$args['status'] = sanitize_key( $filters['status'] );
		}

		if ( ! empty( $filters['customer'] ) ) {
			$customer_val = sanitize_text_field( $filters['customer'] );
			if ( is_numeric( $customer_val ) ) {
				$args['customer_id'] = absint( $customer_val );
			} else {
				$user = get_user_by( 'email', $customer_val );
				if ( $user ) {
					$args['customer_id'] = $user->ID;
				} else {
					$args['customer_id'] = -1;
				}
			}
		}

		if ( ! empty( $filters['date_from'] ) || ! empty( $filters['date_to'] ) ) {
			if ( ! empty( $filters['date_from'] ) ) {
				$args['date_created'] = '>=' . sanitize_text_field( $filters['date_from'] );
			}
			if ( ! empty( $filters['date_to'] ) ) {
				if ( isset( $args['date_created'] ) ) {
					$args['date_created'] = sanitize_text_field( $filters['date_from'] ) . '...' . sanitize_text_field( $filters['date_to'] );
				} else {
					$args['date_created'] = '<=' . sanitize_text_field( $filters['date_to'] );
				}
			}
		}

		$subscriptions = wc_get_orders( $args );

		if ( is_array( $subscriptions ) ) {
			foreach ( $subscriptions as $subscription ) {
				$row = array();

				foreach ( $columns as $column ) {
					$row[] = CsvValueSanitizer::sanitize_value( $this->get_column_value( $subscription, $column ) );
				}

				fputcsv( $output, CsvValueSanitizer::sanitize_row( $row ), $delimiter );
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct output stream close.
		fclose( $output );
		exit;
	}

	private function get_column_value( $subscription, $column ) {
		switch ( $column ) {
			case 'subscription_id':
				return $subscription->get_id();
			case 'status':
				return $subscription->get_status();
			case 'customer_id':
				return $subscription->get_customer_id();
			case 'billing_email':
				return $subscription->get_billing_email();
			case 'parent_order_id':
				if ( method_exists( $subscription, 'get_parent_id' ) ) {
					return $subscription->get_parent_id();
				}
				return $subscription->get_meta( '_parent_id' );
			case 'billing_first_name':
				return $subscription->get_billing_first_name();
			case 'billing_last_name':
				return $subscription->get_billing_last_name();
			case 'product_name':
				$items = $subscription->get_items();
				$names = array();
				foreach ( $items as $item ) {
					$names[] = $item->get_name();
				}
				return implode( ', ', $names );
			case 'product_sku':
				$items = $subscription->get_items();
				$skus  = array();
				foreach ( $items as $item ) {
					$product = $item->get_product();
					if ( $product ) {
						$skus[] = $product->get_sku();
					}
				}
				return implode( ', ', array_filter( $skus ) );
			case 'total':
				return $subscription->get_total();
			case 'order_currency':
				return $subscription->get_currency();
			case 'date_created':
				return $subscription->get_date_created() ? $subscription->get_date_created()->date( 'Y-m-d H:i:s' ) : '';
			case 'next_payment_date':
				if ( method_exists( $subscription, 'get_date' ) ) {
					return $subscription->get_date( 'next_payment' );
				}
				return $subscription->get_meta( '_schedule_next_payment' );
			case 'end_date':
				if ( method_exists( $subscription, 'get_date' ) ) {
					return $subscription->get_date( 'end' );
				}
				return $subscription->get_meta( '_schedule_end' );
			case 'payment_method':
				return $subscription->get_payment_method_title();
			default:
				return '';
		}
	}
}

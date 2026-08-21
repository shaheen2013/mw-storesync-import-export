<?php

namespace MW\WooImportExport\Exporter;

if (! defined('ABSPATH')) {
	exit;
}

class QueryBuilder
{
	public function get_next_batch($offset = 0, $limit = 100, array $filters = array())
	{
		$normalized_statuses = array();

		$args = array(
			'limit'   => $limit,
			'offset'  => absint($offset),
			'orderby' => 'ID',
			'order'   => 'ASC',
			'return'  => 'objects',
			'type'    => 'shop_order',
		);

		if (! empty($filters['status'])) {
			$statuses = array_filter(array_map('sanitize_key', (array) $filters['status']));
			if (! empty($statuses)) {
				foreach ($statuses as $status) {
					// Accept both 'wc-completed' and 'completed' from the UI and normalize to the
					// short form expected by wc_get_orders (status keys without the 'wc-' prefix).
					// Use a prefix-aware replacement instead of ltrim which strips any of the
					// provided characters and can corrupt short status names like 'completed'.
					$status_key = preg_replace('/^wc\-/', '', $status);
					$status_key = sanitize_key($status_key);
					if ('' !== $status_key) {
						$normalized_statuses[] = $status_key;
					}
				}

				$normalized_statuses = array_values(array_unique($normalized_statuses));
				if (! empty($normalized_statuses)) {
					$args['status'] = $normalized_statuses;
					$post_statuses = array_map(function ($s) {
						return 'wc-' . $s;
					}, $normalized_statuses);
					$args['post_status'] = $post_statuses;
				}
			}
		}

		if (! empty($filters['order_ids'])) {
			$order_ids = array_filter(array_map('absint', explode(',', $filters['order_ids'])));
			if (! empty($order_ids)) {
				$args['include'] = $order_ids;
			}
		}

		if (! empty($filters['product'])) {
			$product_value = sanitize_text_field($filters['product']);

			if (ctype_digit($product_value)) {
				$args['product_id'] = absint($product_value);
			} elseif (function_exists('wc_get_product_id_by_sku')) {
				$product_id = wc_get_product_id_by_sku($product_value);

				if ($product_id) {
					$args['product_id'] = absint($product_id);
				}
			}
		}

		if (! empty($filters['customer'])) {
			$customer_value = sanitize_text_field($filters['customer']);

			if (ctype_digit($customer_value)) {
				$args['customer'] = absint($customer_value);
			} else {
				$args['billing_email'] = $customer_value;
			}
		}

		if (! empty($filters['coupons'])) {
			$coupons = array_filter(array_map('trim', explode(',', $filters['coupons'])));
			if (! empty($coupons)) {
				$args['coupon'] = $coupons;
			}
		}

		if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
			$date_created = array();

			if (! empty($filters['date_from']) && strtotime($filters['date_from'])) {
				$date_created['after'] = gmdate('Y-m-d 00:00:00', strtotime($filters['date_from']));
			}

			if (! empty($filters['date_to']) && strtotime($filters['date_to'])) {
				$date_created['before'] = gmdate('Y-m-d 23:59:59', strtotime($filters['date_to']));
			}

			if (! empty($date_created)) {
				$args['date_created'] = $date_created;
			}
		}

		$orders = wc_get_orders($args);

		// Defensive: if wc_get_orders ignored the status filter for any reason, enforce it
		// here by keeping only orders whose status matches the normalized list.
		if (! empty($normalized_statuses) && is_array($orders)) {
			$orders = array_values(array_filter($orders, function ($order) use ($normalized_statuses) {
				if (! is_object($order) || ! method_exists($order, 'get_status')) {
					return false;
				}

				return in_array($order->get_status(), $normalized_statuses, true);
			}));
		}

		return $orders;
	}
}

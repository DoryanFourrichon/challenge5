<?php

namespace ACA\WC;

use WC_Order;

final class Helper {

	/**
	 * @param int[]|int $post_ids
	 * @param string    $field
	 *
	 * @return array [ int $post_id => string $post_field ]
	 */
	public function get_editable_posts_values( $post_ids, $field = 'post_title' ) {
		$value = [];

		if ( $post_ids ) {
			foreach ( (array) $post_ids as $id ) {
				$value[ $id ] = get_post_field( (string) $field, (int) $id );
			}
		}

		return $value;
	}

	/**
	 * @param int          $user_id
	 * @param string|array $status
	 *
	 * @return int[]
	 */
	public function get_order_ids_by_user( $user_id, $status ) {
		$args = [
			'fields'         => 'ids',
			'post_type'      => 'shop_order',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_query'     => [
				[
					'key'   => '_customer_user',
					'value' => (int) $user_id,
				],
			],
		];

		if ( $status ) {
			$args['post_status'] = $status;
		}

		$order_ids = get_posts( $args );

		if ( ! $order_ids ) {
			return [];
		}

		return $order_ids;
	}

	/**
	 * @param int          $user_id
	 * @param string|array $status
	 *
	 * @return WC_Order[]|array
	 */
	public function get_orders_by_user( $user_id, $status = [ 'wc-completed', 'wc-processing' ] ) {
		$orders = [];

		foreach ( $this->get_order_ids_by_user( (int) $user_id, $status ) as $order_id ) {
			$orders[] = wc_get_order( $order_id );
		}

		return $orders;
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function get_product_ids_by_order( $order_id ) {
		global $wpdb;

		$product_ids = $wpdb->get_col(
			$wpdb->prepare( "
				SELECT DISTINCT om.meta_value
				FROM {$wpdb->prefix}woocommerce_order_items AS oi
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON ( oi.order_item_id = om.order_item_id )
				WHERE om.meta_key = '_product_id'
				AND oi.order_id = %d
				ORDER BY om.meta_value;
				",
				(int) $order_id
			)
		);

		return $product_ids;
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function get_product_or_variation_ids_by_order( $order_id ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare( "
				SELECT om.order_item_id as oid, om.meta_value as product_id, om2.meta_value as variation_id
				FROM {$wpdb->prefix}woocommerce_order_items AS oi
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om ON ( oi.order_item_id = om.order_item_id )
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om2 ON ( oi.order_item_id = om2.order_item_id )
				WHERE om.meta_key = '_product_id' 
				AND om2.meta_key ='_variation_id'
				AND oi.order_id = %d
				",
				(int) $order_id
			)
		);

		$product_ids = [];

		foreach ( $results as $result ) {
			$product_ids[] = $result->variation_id ?: $result->product_id;
		}

		return $product_ids;
	}

	/**
	 * @param int $coupon_id
	 *
	 * @return int[]
	 */
	public function get_order_ids_by_coupon_id( $coupon_id ) {
		return $this->get_order_ids_by_coupon_code( ac_helper()->post->get_raw_post_title( (int) $coupon_id ) );
	}

	/**
	 * @param string $coupon_code
	 *
	 * @return int[]
	 */
	public function get_order_ids_by_coupon_code( $coupon_code ) {
		global $wpdb;

		$table = $wpdb->prefix . 'woocommerce_order_items';

		$sql = "
			SELECT {$table}.order_id
			FROM {$table}
			WHERE order_item_type = 'coupon'
			AND order_item_name = %s
		";

		$sql = $wpdb->prepare( $sql, (string) $coupon_code );

		return (array) $wpdb->get_col( $sql );
	}

	/**
	 * @param string $code
	 *
	 * @return string|null
	 */
	public function get_coupon_id_from_code( $code ) {
		global $wpdb;

		$sql = $wpdb->prepare( "
			SELECT ID 
			FROM $wpdb->posts 
			WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' 
			ORDER BY post_date DESC LIMIT 1;
			",
			(string) $code
		);

		return $wpdb->get_var( $sql );
	}

	/**
	 * @param int               $user_id
	 * @param string|array|null $status
	 *
	 * @return array
	 */
	public function get_totals_for_user( $user_id, $status = null ) {
		$totals = [];

		foreach ( $this->get_orders_by_user( (int) $user_id, $status ) as $order ) {
			if ( ! $order->get_total() ) {
				continue;
			}

			$currency = $order->get_currency();

			if ( ! isset( $totals[ $currency ] ) ) {
				$totals[ $currency ] = 0;
			}

			$totals[ $currency ] += $order->get_total();
		}

		return $totals;
	}

	/**
	 * @param int   $product_id
	 * @param array $status
	 *
	 * @return int[]
	 */
	public function get_orders_ids_by_product_id( $product_id, $status = [ 'wc-completed' ] ) {
		global $wpdb;

		$status_in = sprintf(
			"'%s'",
			implode( "','", array_map( 'esc_sql', $status )
			)
		);

		$orders_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
		        SELECT DISTINCT woi.order_id
		        FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim, 
		             {$wpdb->prefix}woocommerce_order_items as woi, 
		             {$wpdb->prefix}posts as p
		        WHERE  woi.order_item_id = woim.order_item_id
		        AND woi.order_id = p.ID
		        AND p.post_status IN ( $status_in )
		        AND woim.meta_key = '_product_id'
		        AND woim.meta_value = %d
		        ORDER BY woi.order_item_id DESC
		        ",
				(int) $product_id
			)
		);

		return $orders_ids;
	}

}
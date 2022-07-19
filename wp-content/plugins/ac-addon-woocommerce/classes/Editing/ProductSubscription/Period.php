<?php

namespace ACA\WC\Editing\ProductSubscription;

use AC\Request;
use ACA\WC\Editing\EditValue;
use ACA\WC\Editing\StorageModel;
use ACA\WC\Editing\View\SubscriptionPeriod;
use ACP;
use RuntimeException;
use WC_Product_Subscription;

class Period implements ACP\Editing\Service {

	const KEY_INTERVAL = '_subscription_period_interval';
	const KEY_PERIOD = '_subscription_period';

	public function get_value( $product_id ) {
		$product = wc_get_product( $product_id );

		return $product instanceof WC_Product_Subscription
			? [
				'interval' => $product->get_meta( self::KEY_INTERVAL ),
				'period'   => $product->get_meta( self::KEY_PERIOD ),
			]
			: null;
	}

	public function get_view( $context ) {
		return new SubscriptionPeriod( wcs_get_subscription_period_interval_strings(), wcs_get_subscription_period_strings() );
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );

		if ( ! $product instanceof WC_Product_Subscription ) {
			throw new RuntimeException( __( 'Product Type not supported', 'codepress-admin-columns' ) );
		}

		$value = $request->get( 'value', [] );

		update_post_meta( $request->get( 'id' ), self::KEY_INTERVAL, isset( $value['interval'] ) ? $value['interval'] : '' );
		update_post_meta( $request->get( 'id' ), self::KEY_PERIOD, isset( $value['period'] ) ? $value['period'] : '' );

		return true;
	}

}
<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;

class BackordersAllowed implements ACP\Editing\Service {

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		// Only items that have manage stock enabled can have back orders
		if ( ! $product->managing_stock() ) {
			return null;
		}

		return $product->get_backorders();
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\Select( $this->get_backorder_options() );
	}

	public function update( Request $request ) {
		if ( ! array_key_exists( $request->get( 'value', '' ), $this->get_backorder_options() ) ) {
			return false;
		}

		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_backorders( $request->get( 'value', '' ) );

		return $product->save() > 0;
	}

	private function get_backorder_options() {
		return [
			'no'     => __( 'Do not allow', 'woocommerce' ),
			'notify' => __( 'Allow, but notify customer', 'woocommerce' ),
			'yes'    => __( 'Allow', 'woocommerce' ),
		];
	}

}
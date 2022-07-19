<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;

class Weight implements ACP\Editing\Service {

	public function get_value( $post_id ) {
		$product = wc_get_product( $post_id );

		return $product && ! $product->is_virtual()
			? $product->get_weight()
			: null;
	}

	public function get_view( $context ) {
		return ( new ACP\Editing\View\Number() )->set_step( 'any' )->set_min( 0 );
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_weight( $request->get( 'value' ) );

		return $product->save() > 0;
	}

}
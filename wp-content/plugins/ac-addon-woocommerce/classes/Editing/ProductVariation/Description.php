<?php

namespace ACA\WC\Editing\ProductVariation;

use AC\Request;
use ACP;
use WC_Product_Variation;

class Description implements ACP\Editing\Service {

	public function get_view( $context ) {
		return ( new ACP\Editing\View\TextArea() )->set_clear_button( true );
	}

	public function get_value( $post_id ) {
		$product = new WC_Product_Variation( $post_id );

		return $product->get_description();
	}

	public function update( Request $request ) {
		$product = new WC_Product_Variation( $request->get( 'id' ) );
		$product->set_description( $request->get( 'value' ) );

		return $product->save() > 0;
	}

}
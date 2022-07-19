<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACA\WC\Editing;
use ACP;

class Dimensions implements ACP\Editing\Service {

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		if ( $product->is_virtual() ) {
			return null;
		}

		return (object) [
			'length' => $product->get_length(),
			'width'  => $product->get_width(),
			'height' => $product->get_height(),
		];
	}

	public function get_view( $context ) {
		return new Editing\View\Dimensions();
	}

	public function update( Request $request ) {
		$value = $request->get( 'value' );
		$id = $request->get( 'id' );

		if ( ! is_array( $value ) || ( ! isset( $value['length'], $value['width'], $value['height'] ) ) ) {
			return false;
		}

		$product = wc_get_product( $id );

		if ( $product->is_virtual() ) {
			return false;
		}

		$product->set_length( $value['length'] );
		$product->set_width( $value['width'] );
		$product->set_height( $value['height'] );

		return $product->save() > 0;
	}

}
<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;

class ShippingClass implements ACP\Editing\Service {

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		if ( ! $product || ! $product->needs_shipping() ) {
			return null;
		}

		return $product->get_shipping_class_id();
	}

	public function get_view( $context ) {
		$options = [ '' => __( 'No shipping class', 'codepress-admin-columns' ) ];
		$shipping_classes = get_terms( [ 'taxonomy' => 'product_shipping_class', 'hide_empty' => false ] );

		foreach ( $shipping_classes as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return new ACP\Editing\View\Select( $options );
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_shipping_class_id( $request->get( 'value' ) );

		return $product->save();
	}

}
<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACA\WC\Editing\View;
use ACP;
use WC_Cache_Helper;

class Type implements ACP\Editing\Service {

	/**
	 * @var array
	 */
	private $simple_product_types;

	public function __construct( $simple_product_types ) {
		$this->simple_product_types = $simple_product_types;
	}

	public function get_view( $context ) {
		return new View\Type( $this->simple_product_types );
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return in_array( $product->get_type(), [ 'subscription', 'variable_subscription' ] )
			? null
			: [
				'type'         => $product->get_type(),
				'virtual'      => $product->is_virtual(),
				'downloadable' => $product->is_downloadable(),
			];
	}

	public function update( Request $request ) {
		$value = $request->get( 'value', [] );
		$id = $request->get( 'id' );

		if ( isset( $value['type'] ) ) {
			wp_set_object_terms( $id, $value['type'], 'product_type' );
		}

		$cache_key = WC_Cache_Helper::get_cache_prefix( 'product_' . $id ) . '_type_' . $id;
		wp_cache_delete( $cache_key, 'products' );

		$product = wc_get_product( $id );

		if ( isset( $value['downloadable'] ) ) {
			$product->set_downloadable( $value['downloadable'] );
		}

		if ( isset( $value['virtual'] ) ) {
			$product->set_virtual( $value['virtual'] );
		}

		return $product->save();
	}

}
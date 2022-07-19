<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;
use RuntimeException;
use WC_Data_Exception;

class Visibility implements ACP\Editing\Service {

	public function get_view( $context ) {
		return new ACP\Editing\View\Select( wc_get_product_visibility_options() );
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return $product ? $product->get_catalog_visibility() : null;
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );

		try {
			$product->set_catalog_visibility( $request->get( 'value' ) );
		} catch ( WC_Data_Exception $e ) {
			throw new RuntimeException( $e->getMessage() );
		}

		return $product->save() > 0;
	}

}
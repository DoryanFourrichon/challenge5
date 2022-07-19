<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;
use RuntimeException;
use WC_Data_Exception;

class SKU implements ACP\Editing\Service {

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return $product->get_sku();
	}

	public function get_view( $context ) {
		return ( new ACP\Editing\View\Text() )->set_clear_button( true );
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id', 0 ) );

		try {
			$product->set_sku( $request->get( 'value', '' ) );
		} catch ( WC_Data_Exception $e ) {
			throw new RuntimeException( $e->getMessage() );
		}

		return $product->save() > 0;
	}

}
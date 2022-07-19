<?php

namespace ACA\WC\Editing\Product;

use AC\Helper\Select\Option;
use AC\Request;
use AC\Type\ToggleOptions;
use ACP;

/**
 * @since 3.0
 */
class SoldIndividually implements ACP\Editing\Service {

	public function get_view( $context ) {
		return new ACP\Editing\View\Toggle(
			new ToggleOptions(
				new Option( 'yes' ), new Option( 'no' )
			)
		);
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return $product->get_sold_individually() ? 'yes' : 'no';
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_sold_individually( $request->get( 'value' ) );

		return $product->save() > 0;
	}

}
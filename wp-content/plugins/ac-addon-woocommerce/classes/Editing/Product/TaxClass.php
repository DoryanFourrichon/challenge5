<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;
use WC_Tax;

class TaxClass implements ACP\Editing\Service {

	public function get_view( $context ) {
		$options = [ '' => __( 'Standard', 'codepress-admin-columns' ) ];

		foreach ( WC_Tax::get_tax_classes() as $tax_class ) {
			$options[ WC_Tax::format_tax_rate_class( $tax_class ) ] = $tax_class;
		}

		return new ACP\Editing\View\Select( $options );
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return $product->get_tax_class( $id );
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_tax_class( $request->get( 'value' ) );

		return $product->save() > 0;
	}

}
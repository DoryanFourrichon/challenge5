<?php

namespace ACA\WC\Editing\ProductVariation;

use AC\Request;
use ACA\WC\Column;
use ACP;

/**
 * @property Column\ProductVariation\TaxClass $column
 */
class TaxClass implements ACP\Editing\Service {

	/**
	 * @var array
	 */
	private $tax_classes;

	public function __construct( $tax_classes ) {
		$this->tax_classes = $tax_classes;
	}

	public function get_value( $id ) {
		return get_post_meta( $id, '_tax_class', true );
	}

	public function get_view( $context ) {
		$options = [ 'parent' => __( 'Use Product Tax Class', 'codepress-admin-columns' ) ];
		$options = array_merge( $options, $this->tax_classes );

		return new ACP\Editing\View\Select( $options );
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_tax_class( $request->get( 'value' ) );

		return $product->save() > 0;
	}

}
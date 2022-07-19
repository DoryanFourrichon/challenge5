<?php

namespace ACA\WC\Editing\Product\Attributes;

use AC\Request;
use ACP;
use WC_Product_Attribute;

class Taxonomy extends ACP\Editing\Service\Post\Taxonomy {

	public function __construct( $taxonomy ) {
		parent::__construct( $taxonomy, false );
	}

	public function get_view( $context ) {
		$view  = parent::get_view( $context );
		$view->set_multiple( true );

		return $view;
	}

	public function update( Request $request ) {
		$this->maybe_attach_taxonomy_attribute( $request->get( 'id' ) );

		return parent::update( $request );
	}

	/**
	 * Attach attribute to product only if was not attached.
	 */
	private function maybe_attach_taxonomy_attribute( $id ) {
		$product = wc_get_product( $id );
		$atts = $product->get_attributes();

		if ( array_key_exists( $this->taxonomy, $atts ) ) {
			return;
		}

		$product_attribute = new WC_Product_Attribute();

		$product_attribute->set_id( wc_attribute_taxonomy_id_by_name( $this->taxonomy ) );
		$product_attribute->set_name( $this->taxonomy );

		$atts[] = $product_attribute;

		$product->set_attributes( $atts );
		$product->save();
	}

}
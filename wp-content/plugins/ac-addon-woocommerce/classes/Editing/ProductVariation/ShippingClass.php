<?php

namespace ACA\WC\Editing\ProductVariation;

use ACA\WC\Editing;

class ShippingClass extends Editing\Product\ShippingClass {

	public function get_view( $context ) {
		$view = parent::get_view( $context );
		$options = $view->get_arg( 'options' );
		$options[''] = __( 'Use Product Shipping Class', 'codepress-admin-columns' );
		$view->set_options( $options );

		return $view;
	}

}
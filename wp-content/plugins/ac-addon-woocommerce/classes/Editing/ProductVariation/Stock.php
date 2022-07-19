<?php

namespace ACA\WC\Editing\ProductVariation;

use ACA\WC\Editing;

class Stock extends Editing\Product\Stock {

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return (object) [
			'type'     => $product->get_manage_stock() && $this->is_manage_stock_enabled() ? 'manage_stock' : $product->get_stock_status(),
			'quantity' => $product->get_stock_quantity(),
		];
	}

}
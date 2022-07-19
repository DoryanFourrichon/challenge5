<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;
use RuntimeException;
use WC_Data_Exception;

class TaxStatus implements ACP\Editing\Service {

	/**
	 * @var array
	 */
	private $statuses;

	public function __construct( $statuses ) {
		$this->statuses = $statuses;
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\Select( $this->statuses );
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return $product->get_tax_status();
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );

		try {
			$product->set_tax_status( $request->get( 'value' ) );
		} catch ( WC_Data_Exception $e ) {
			throw new RuntimeException( $e->getMessage() );
		}

		return $product->save() > 0;
	}

}
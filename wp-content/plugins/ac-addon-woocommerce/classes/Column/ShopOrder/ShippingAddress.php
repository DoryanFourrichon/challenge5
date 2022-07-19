<?php

namespace ACA\WC\Column\ShopOrder;

use AC;
use ACA\WC\Export;
use ACP;

/**
 * @since 2.0
 */
class ShippingAddress extends AC\Column
	implements ACP\Export\Exportable {

	public function __construct() {
		$this->set_type( 'shipping_address' );
		$this->set_original( true );
	}

	public function export() {
		return new Export\ShopOrder\ShippingAddress( $this );
	}

}
<?php

namespace ACA\WC\Settings\ShopOrder;

use AC;

class Status extends AC\Settings\Column {

	const NAME = 'order_status';

	protected function define_options() {
		$statuses = wc_get_order_statuses();
		$default_value = [];

		if ( array_key_exists( 'wc-completed', $statuses ) ) {
			$default_value[] = 'wc-completed';
		}

		return [ self::NAME => $default_value ];
	}

	public function create_view() {
		$select = $this->create_element( 'multi-select' )
		               ->set_options( wc_get_order_statuses() );

		return new AC\View( [
			'label'   => __( 'Order Status', 'codepress-admin-columns' ),
			'setting' => $select,
		] );
	}

	public function get_order_status() {
		return (array) $this->order_status;
	}

	public function set_order_status( $status ) {
		$this->order_status = $status;
	}

}
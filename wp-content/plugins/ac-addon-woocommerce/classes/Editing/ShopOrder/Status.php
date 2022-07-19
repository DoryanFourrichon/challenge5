<?php

namespace ACA\WC\Editing\ShopOrder;

use AC\Request;
use ACP;

class Status implements ACP\Editing\Service {

	public function get_view( $context ) {
		return new ACP\Editing\View\Select( wc_get_order_statuses() );
	}

	public function get_value( $id ) {
		$status = wc_get_order( $id )->get_status();

		if ( strpos( $status, 'wc-' ) !== 0 ) {
			$status = 'wc-' . $status;
		}

		return strpos( $status, 'wc-' ) !== 0
			? 'wc-' . $status
			: $status;
	}

	public function update( Request $request ) {
		return wc_get_order( $request->get( 'id' ) )->update_status( $request->get( 'value' ) );
	}

}

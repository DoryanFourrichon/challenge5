<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC\Request;
use ACP;
use WC_Coupon;

class ExpiryDate implements ACP\Editing\Service {

	public function get_view( $context ) {
		return ( new ACP\Editing\View\Date() )->set_clear_button( true );
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );
		$date = $coupon->get_date_expires();

		return $date
			? $date->date( 'Y-m-d' )
			: false;
	}

	public function update( Request $request ) {
		$value = $request->get( 'value' ) ? strtotime( $request->get( 'value' ) ) : '';
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_date_expires( $value );

		return $coupon->save() > 0;
	}

}
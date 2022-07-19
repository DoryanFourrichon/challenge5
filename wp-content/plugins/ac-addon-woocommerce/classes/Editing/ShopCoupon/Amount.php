<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC\Request;
use ACP;
use WC_Coupon;

class Amount implements ACP\Editing\Service {

	public function get_view( $context ) {
		return ( new ACP\Editing\View\Number() )->set_min( 0 )->set_step( 'any' );
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );

		return $coupon->get_amount();
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_amount( $request->get( 'value' ) );

		return $coupon->save() > 0;
	}

}
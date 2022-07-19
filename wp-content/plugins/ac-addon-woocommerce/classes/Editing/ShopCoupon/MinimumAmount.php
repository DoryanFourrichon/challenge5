<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC\Request;
use ACP;
use WC_Coupon;

class MinimumAmount implements ACP\Editing\Service {

	public function get_view( $context ) {
		$view = new ACP\Editing\View\Number();

		return $view->set_step( 'any' )->set_min( 0 );
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );

		return $coupon->get_minimum_amount();
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_minimum_amount( $request->get( 'value' ) );

		return $coupon->save() > 0;
	}

}
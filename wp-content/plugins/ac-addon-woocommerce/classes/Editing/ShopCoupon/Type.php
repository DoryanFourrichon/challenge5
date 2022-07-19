<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC\Request;
use ACP;
use WC_Coupon;

class Type implements ACP\Editing\Service {

	public function get_view( $context ) {
		return new ACP\Editing\View\Select( wc_get_coupon_types() );
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );

		return $coupon->get_discount_type();
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_discount_type( $request->get( 'value' ) );

		return $coupon->save() > 0;
	}

}
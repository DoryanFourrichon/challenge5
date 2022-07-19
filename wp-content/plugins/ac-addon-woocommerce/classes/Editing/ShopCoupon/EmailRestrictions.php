<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC\Request;
use ACP;
use WC_Coupon;

class EmailRestrictions implements ACP\Editing\Service {

	public function get_view( $context ) {
		return ( new ACP\Editing\View\MultiInput() )->set_clear_button( true );
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );

		return $coupon->get_email_restrictions();
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_email_restrictions( $request->get( 'value' ) );

		return $coupon->save() > 0;
	}

}
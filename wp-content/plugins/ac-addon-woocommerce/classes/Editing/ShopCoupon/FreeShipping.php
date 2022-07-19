<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC\Helper\Select\Option;
use AC\Request;
use AC\Type\ToggleOptions;
use ACP;
use WC_Coupon;

class FreeShipping implements ACP\Editing\Service {

	public function get_view( $context ) {
		return new ACP\Editing\View\Toggle(
			new ToggleOptions(
				new Option( 'no' ), new Option( 'yes' )
			)
		);
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );

		return $coupon->get_free_shipping() ? 'yes' : 'no';
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_free_shipping( 'yes' === $request->get( 'value' ) );

		return $coupon->save() > 0;
	}

}
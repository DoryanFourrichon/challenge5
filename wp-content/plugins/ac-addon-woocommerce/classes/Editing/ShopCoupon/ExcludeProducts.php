<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC;
use AC\Request;
use ACA\WC\Editing\ValuesFromMethodRequestTrait;
use ACA\WC\Helper\Select;
use ACP;
use WC_Coupon;

class ExcludeProducts implements ACP\Editing\Service, ACP\Editing\PaginatedOptions {

	use ValuesFromMethodRequestTrait;

	public function get_view( $context ) {
		$view = ( new ACP\Editing\View\AjaxSelect() )
			->set_multiple( true )
			->set_clear_button( true );

		if ( $context === self::CONTEXT_BULK ) {
			$view->has_methods( true )->set_revisioning( false );
		}

		return $view;
	}

	public function get_value( $id ) {
		$coupon = new WC_Coupon( $id );

		return ac_addon_wc_helper()->get_editable_posts_values( $coupon->get_excluded_product_ids() );
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_excluded_product_ids( $this->get_values_from_request( $request ) );

		return $coupon->save() > 0;
	}

	public function get_paginated_options( $s, $paged, $id = null ) {
		$entities = new Select\Entities\Product( compact( 's', 'paged' ) );

		return new AC\Helper\Select\Options\Paginated(
			$entities,
			new Select\Formatter\ProductTitleAndSKU( $entities )
		);
	}

}
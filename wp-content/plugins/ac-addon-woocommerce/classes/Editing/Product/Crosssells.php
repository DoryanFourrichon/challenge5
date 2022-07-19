<?php

namespace ACA\WC\Editing\Product;

use AC;
use AC\Request;
use ACA\WC\Editing\ValuesFromMethodRequestTrait;
use ACA\WC\Helper\Select;
use ACP;
use ACP\Editing\PaginatedOptions;

class Crosssells
	implements ACP\Editing\Service, PaginatedOptions {

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

	public function get_paginated_options( $s, $paged, $id = null ) {
		$entities = new Select\Entities\Product( compact( 's', 'paged' ) );

		return new AC\Helper\Select\Options\Paginated(
			$entities,
			new Select\Formatter\ProductTitleAndSKU( $entities )
		);
	}

	public function get_value( $id ) {
		return ac_addon_wc_helper()->get_editable_posts_values( wc_get_product( $id )->get_cross_sell_ids() );
	}

	public function update( Request $request ) {
		$id = (int) $request->get( 'id' );
		$product = wc_get_product( $id );

		$product->set_cross_sell_ids( $this->get_values_from_request( $request ) );

		return $product->save() > 0;
	}

}
<?php

namespace ACA\WC\Editing\Product;

use AC;
use AC\Request;
use ACA\WC\Editing\ValuesFromMethodRequestTrait;
use ACA\WC\Helper\Select;
use ACP;

class Upsells implements ACP\Editing\PaginatedOptions, ACP\Editing\Service {

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
		$product = wc_get_product( $id );

		return ac_addon_wc_helper()->get_editable_posts_values( (array) $product->get_upsell_ids() );
	}

	public function update( Request $request ) {
		$id = (int) $request->get( 'id' );
		$product = wc_get_product( $id );

		$product->set_upsell_ids( $this->get_values_from_request( $request ) );

		return $product->save() > 0;
	}

	public function get_paginated_options( $s, $paged, $id = null ) {
		$entities = new Select\Entities\Product( compact( 's', 'paged' ) );

		return new AC\Helper\Select\Options\Paginated(
			$entities,
			new Select\Formatter\ProductTitleAndSKU( $entities )
		);
	}

}
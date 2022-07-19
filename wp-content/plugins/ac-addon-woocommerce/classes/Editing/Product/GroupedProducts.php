<?php

namespace ACA\WC\Editing\Product;

use AC;
use AC\Request;
use ACA\WC\Helper\Select;
use ACP;
use ACP\Editing\Storage;

class GroupedProducts extends ACP\Editing\Service\BasicStorage implements ACP\Editing\PaginatedOptions {

	public function __construct() {
		parent::__construct( new Storage\Post\Meta( '_children' ) );
	}

	public function get_view( $context ) {
		return ( new ACP\Editing\View\AjaxSelect() )->set_multiple( true )->set_clear_button( true );
	}

	public function get_paginated_options( $s, $paged, $id = null ) {
		$entities = new Select\Entities\Product( compact( 's', 'paged' ) );

		return new AC\Helper\Select\Options\Paginated(
			$entities,
			new Select\Formatter\ProductTitleAndSKU( $entities )
		);
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		if ( 'grouped' !== $product->get_type() ) {
			return null;
		}

		return ac_addon_wc_helper()->get_editable_posts_values( $product->get_children() );
	}

	public function update( Request $request ) {
		$request->get_parameters()->set( 'value', array_map( 'intval', (array) $request->get( 'value' ) ) );

		return parent::update( $request );
	}

}
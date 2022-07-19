<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;

class Gallery implements ACP\Editing\Service {

	public function get_view( $context ) {
		return ( new ACP\Editing\View\Image() )->set_multiple( true )->set_clear_button( true );
	}

	public function get_value( $id ) {
		$product = wc_get_product( $id );

		return $product->get_gallery_image_ids();
	}

	public function update( Request $request ) {
		$product = wc_get_product( $request->get( 'id' ) );
		$product->set_gallery_image_ids( $request->get( 'value' ) );

		return $product->save() > 0;
	}

}
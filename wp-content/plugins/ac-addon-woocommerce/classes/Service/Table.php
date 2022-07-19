<?php

namespace ACA\WC\Service;

use AC\ListScreen;
use AC\Registrable;
use ACA\WC\ListScreen\Product;

class Table implements Registrable {

	public function register() {
		add_filter( 'acp/sorting/remember_last_sorting_preference', [ $this, 'disable_product_sorting_mode_preference' ], 10, 2 );
		add_filter( 'acp/sticky_header/enable', [ $this, 'disable_sticky_headers' ] );
	}

	public function disable_sticky_headers( $enabled ) {
		return 'product' === filter_input( INPUT_GET, 'post_type' ) && 'menu_order title' === filter_input( INPUT_GET, 'orderby' )
			? false
			: $enabled;
	}

	public function disable_product_sorting_mode_preference( $enabled, ListScreen $list_screen ) {
		if ( $list_screen instanceof Product && 'menu_order title' === filter_input( INPUT_GET, 'orderby' ) ) {
			return false;
		}

		return $enabled;
	}

}
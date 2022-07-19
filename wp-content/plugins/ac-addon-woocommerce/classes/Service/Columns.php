<?php

namespace ACA\WC\Service;

use AC\ListScreen;
use AC\Registrable;

class Columns implements Registrable {

	public function register() {
		add_action( 'ac/column_types', [ $this, 'register_columns' ] );
	}

	/**
	 * @param ListScreens $list_screen
	 */
	public function register_columns( ListScreen $list_screen ) {
		if ( $list_screen instanceof ListScreen\User ) {
			$list_screen->register_column_types_from_dir( 'ACA\WC\Column\User' );
		}

		if ( $list_screen instanceof ListScreen\Comment ) {
			$list_screen->register_column_types_from_dir( 'ACA\WC\Column\Comment' );
		}
	}

}
<?php

namespace ACA\WC\Service;

use AC\Groups;
use AC\Registrable;

class ListScreenGroups implements Registrable {

	public function register() {
		add_action( 'ac/list_screen_groups', [ $this, 'register_list_screen_groups' ] );
	}

	public function register_list_screen_groups( Groups $groups ) {
		$groups->register_group( 'woocommerce', __( 'WooCommerce', 'codepress-admin-columns' ), 7 );
	}

}
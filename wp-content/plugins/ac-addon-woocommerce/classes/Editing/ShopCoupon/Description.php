<?php

namespace ACA\WC\Editing\ShopCoupon;

use ACP;
use ACP\Editing\Storage;

class Description extends ACP\Editing\Service\BasicStorage {

	public function __construct() {
		parent::__construct( new Storage\Post\Field( 'post_excerpt' ) );
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\TextArea();
	}

}
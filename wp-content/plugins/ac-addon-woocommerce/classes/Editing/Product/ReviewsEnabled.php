<?php

namespace ACA\WC\Editing\Product;

use AC\Helper\Select\Option;
use AC\Type\ToggleOptions;
use ACP;
use ACP\Editing\Storage;

class ReviewsEnabled extends ACP\Editing\Service\BasicStorage {

	public function __construct() {
		parent::__construct( new Storage\Post\Field( 'comment_status' ) );
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\Toggle(
			new ToggleOptions(
				new Option( 'closed' ), new Option( 'open' )
			)
		);
	}
}
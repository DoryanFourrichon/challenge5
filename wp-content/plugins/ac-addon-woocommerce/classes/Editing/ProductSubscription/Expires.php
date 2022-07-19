<?php

namespace ACA\WC\Editing\ProductSubscription;

use AC;
use ACA\WC;
use ACA\WC\Editing\EditValue;
use ACA\WC\Editing\StorageModel;
use ACP;
use ACP\Editing\Storage;
use WC_Product_Subscription;

class Expires extends ACP\Editing\Service\BasicStorage implements ACP\Editing\PaginatedOptions {

	public function __construct( ) {
		parent::__construct( new Storage\Post\Meta( '_subscription_length' ) );
	}

	public function get_value( $product_id ) {
		$product = wc_get_product( $product_id );

		return $product instanceof WC_Product_Subscription
			? parent::get_value( $product_id )
			: null;
	}

	public function get_view( $context ) {
		return ( new ACP\Editing\View\AjaxSelect() )->set_clear_button( true );
	}

	public function get_paginated_options( $search, $page, $id = null ) {
		$period = $id ? get_post_meta( $id, '_subscription_period', true ) : 'day';

		return new AC\Helper\Select\Options\Paginated(
			new WC\Helper\Select\SinglePage(),
			AC\Helper\Select\Options::create_from_array( wcs_get_subscription_ranges( $period ) )
		);
	}

}
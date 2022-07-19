<?php

namespace ACA\WC\Editing\ProductSubscription;

use AC;
use ACA\WC\Editing\EditValue;
use ACA\WC\Editing\StorageModel;
use ACP;
use WC_Product_Subscription;

class Limit extends ACP\Editing\Service\BasicStorage {

	/**
	 * @var array
	 */
	private $options;

	public function __construct( $options ) {
		$this->options = $options;

		parent::__construct( new ACP\Editing\Storage\Post\Meta( '_subscription_limit' ) );
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\Select( $this->options );
	}

	public function get_value( $product_id ) {
		$product = wc_get_product( $product_id );

		return $product instanceof WC_Product_Subscription
			? parent::get_value( $product_id )
			: null;

	}

}
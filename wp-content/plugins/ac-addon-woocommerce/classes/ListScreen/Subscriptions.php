<?php

namespace ACA\WC\ListScreen;

use ACA\WC\Column\ShopOrder;
use ACA\WC\Column\ShopSubscription;
use ACP;

class Subscriptions extends ACP\ListScreen\Post {

	public function __construct() {
		parent::__construct( 'shop_subscription' );

		$this->set_group( 'woocommerce' );
	}

	protected function register_column_types() {
		parent::register_column_types();

		$columns = [
			new ShopOrder\Address\Shipping(),
			new ShopOrder\Address\Billing(),
			new ShopOrder\Customer(),
			new ShopOrder\CouponsUsed(),
			new ShopOrder\Currency(),
			new ShopOrder\Discount(),
			new ShopOrder\Downloads(),
			new ShopOrder\Product(),
			new ShopOrder\Order(),
			new ShopOrder\Purchased(),
			new ShopOrder\Subtotal(),
			new ShopOrder\Tax(),
			new ShopOrder\Tax(),
			new ShopOrder\Totals(),
			new ShopSubscription\AutoRenewal(),
			new ShopSubscription\EndDate(),
			new ShopSubscription\LastPaymentDate(),
			new ShopSubscription\NextPaymentDate(),
			new ShopSubscription\Orders(),
			new ShopSubscription\OrderItems(),
			new ShopSubscription\RecurringTotal(),
			new ShopSubscription\StartDate(),
			new ShopSubscription\Status(),
			new ShopSubscription\SubscriptionDate(),
			new ShopSubscription\TotalRevenue(),
			new ShopSubscription\TrailEndDate(),
		];

		foreach ( $columns as $column ) {
			$this->register_column_type( $column );
		}
	}

}
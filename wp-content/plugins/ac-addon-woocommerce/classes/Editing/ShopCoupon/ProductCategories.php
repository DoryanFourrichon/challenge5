<?php

namespace ACA\WC\Editing\ShopCoupon;

use AC;
use AC\Request;
use ACA\WC\Editing\ValuesFromMethodRequestTrait;
use ACP;
use ACP\Helper\Select;
use WC_Coupon;

class ProductCategories implements ACP\Editing\Service, ACP\Editing\PaginatedOptions {

	use ValuesFromMethodRequestTrait;

	/**
	 * @var string
	 */
	private $meta_key;

	public function __construct( $meta_key ) {
		$this->meta_key = $meta_key;
	}

	public function get_view( $context ) {
		$view = ( new ACP\Editing\View\AjaxSelect() )
			->set_multiple( true )
			->set_clear_button( true );

		if ( $context === self::CONTEXT_BULK ) {
			$view->has_methods( true )->set_revisioning( false );
		}

		return $view;
	}

	public function get_value( $id ) {
		$term_ids = get_post_meta( $id, $this->meta_key, true );

		if ( empty( $term_ids ) ) {
			return false;
		}

		$values = [];

		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id, 'product_cat' );

			if ( $term ) {
				$values[ $term->term_id ] = htmlspecialchars_decode( $term->name );
			}
		}

		return $values;
	}

	public function update( Request $request ) {
		$coupon = new WC_Coupon( $request->get( 'id' ) );
		$coupon->set_product_categories( $this->get_values_from_request( $request ) );

		return $coupon->save() > 0;
	}

	public function get_paginated_options( $search, $page, $id = null ) {
		$entities = new Select\Entities\Taxonomy( [
			'search'   => $search,
			'page'     => $page,
			'taxonomy' => 'product_cat',
		] );

		return new AC\Helper\Select\Options\Paginated(
			$entities,
			new Select\Formatter\TermName( $entities )
		);
	}

}
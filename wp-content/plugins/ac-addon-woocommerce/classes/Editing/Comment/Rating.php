<?php

namespace ACA\WC\Editing\Comment;

use AC\Request;
use ACP;
use ACP\Editing\Storage;
use WC_Comments;

class Rating extends ACP\Editing\Service\BasicStorage {

	public function __construct() {
		parent::__construct( new Storage\Comment\Meta( 'rating' ) );
	}

	public function get_value( $id ) {
		$comment = get_comment( $id );

		return 'product' === get_post_type( $comment->comment_post_ID )
			? parent::get_value( $id )
			: null;
	}

	public function get_view( $context ) {
		$options = [
			'' => __( 'None', 'codepress-admin-columns' ),
		];

		for ( $i = 1; $i < 6; $i++ ) {
			$options[ $i ] = $i;
		}

		return ( new ACP\Editing\View\Select( $options ) )->set_clear_button( true );
	}

	public function update( Request $request ) {
		$value = absint( $request->get( 'value' ) );

		if ( $value > 5 ) {
			return false;
		}
		$result = parent::update( $request );

		$comment = get_comment( $request->get( 'id' ) );
		$product = wc_get_product( $comment->comment_post_ID );

		// Update average rating for product
		WC_Comments::get_average_rating_for_product( $product );

		return $result;
	}

}
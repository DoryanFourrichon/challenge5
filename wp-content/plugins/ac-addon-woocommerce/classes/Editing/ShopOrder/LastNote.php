<?php

namespace ACA\WC\Editing\ShopOrder;

use AC\Request;
use ACA\WC;
use ACP;

class LastNote implements ACP\Editing\Service {

	/**
	 * @var WC\Column\ShopOrder\Notes
	 */
	private $column;

	public function __construct( WC\Column\ShopOrder\Notes $column ) {
		$this->column = $column;
	}

	public function get_view( $context ) {
		if ( $context === self::CONTEXT_BULK ) {
			return false;
		}

		return new ACP\Editing\View\TextArea();
	}

	public function get_value( $id ) {
		$note = $this->get_last_note_for_order( $id );

		return $note ? $note->content : null;
	}

	private function get_last_note_for_order( $id ) {
		return $this->column->get_last_order_note( $id );
	}

	public function update( Request $request ) {
		$note = $this->get_last_note_for_order( $request->get( 'id' ) );

		return wp_update_comment( [
			'comment_ID'      => $note ? $note->id : 0,
			'comment_content' => $request->get( 'value' ),
		] );
	}

}

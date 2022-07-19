<?php

namespace ACA\WC\Editing;

use AC\Request;

trait ValuesFromMethodRequestTrait {

	public function get_values_from_request( Request $request ) {
		$params = $request->get( 'value' );
		$id = (int) $request->get( 'id' );

		if ( ! isset( $params['method'] ) ) {
			$params = [
				'method' => 'replace',
				'value'  => $params,
			];
		}

		switch ( $params['method'] ) {
			case 'add':
				return array_merge( array_keys( $this->get_value( $id ) ), (array) $params['value'] );

			case 'remove':
				return array_diff( array_keys( $this->get_value( $id ) ), (array) $params['value'] );

			default:
				return (array) $params['value'];
		}
	}

}
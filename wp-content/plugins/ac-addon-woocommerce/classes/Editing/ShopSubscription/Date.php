<?php

namespace ACA\WC\Editing\ShopSubscription;

use AC\Request;
use ACP;
use Exception;
use RuntimeException;

class Date implements ACP\Editing\Service {

	/**
	 * @var string
	 */
	private $date_key;

	/**
	 * @var string
	 */
	private $meta_key;

	public function __construct( $date_key, $meta_key ) {
		$this->date_key = $date_key;
		$this->meta_key = $meta_key;
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\DateTime();
	}

	public function get_value( $id ) {
		return get_post_meta( $id, $this->meta_key, true );
	}

	public function update( Request $request ) {
		$subscription = wcs_get_subscription( $request->get( 'id' ) );

		try {
			$subscription->update_dates( [
				$this->date_key => $request->get( 'value' ),
			], get_option( 'timezone_string' ) );

			$subscription->save();
		} catch ( Exception $exception ) {
			throw new RuntimeException( $exception->getMessage() );
		}

		return true;
	}
}

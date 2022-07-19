<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACA\WC\Editing\EditValue;
use ACA\WC\Editing\StorageModel;
use ACA\WC\Editing\View;
use ACP;
use RuntimeException;
use WP_Error;

class Price implements ACP\Editing\Service {

	const TYPE_SALE = 'sale';
	const TYPE_REGULAR = 'regular';

	/** @var string */
	private $default_type;

	public function __construct( $default_type = 'regular' ) {
		if ( self::TYPE_REGULAR !== $default_type ) {
			$default_type = self::TYPE_SALE;
		}

		$this->default_type = $default_type;
	}

	public function get_view( $context ) {
		return new View\Price( $this->default_type );
	}

	/**
	 * @param int $id
	 *
	 * @return null|array
	 */
	public function get_value( $id ) {
		$product = wc_get_product( $id );

		if ( ! $product || $product->is_type( [ 'variable', 'grouped' ] ) ) {
			return null;
		}

		$from_date = $product->get_date_on_sale_from();
		$to_date = $product->get_date_on_sale_to();

		return [
			self::TYPE_REGULAR => [
				'price' => $product->get_regular_price(),
			],
			self::TYPE_SALE    => [
				'price'         => $product->get_sale_price(),
				'schedule_from' => $from_date ? $from_date->format( 'Y-m-d' ) : '',
				'schedule_to'   => $to_date ? $to_date->format( 'Y-m-d' ) : '',
			],
		];
	}

	public function update( Request $request ) {
		$id = $request->get( 'id' );
		$value = $request->get( 'value', [] );

		switch ( $value['type'] ) {
			case self::TYPE_REGULAR:
				$model = new StorageModel\Product\Price( wc_get_product( $id ), new EditValue\Product\Price( $value ) );

				break;
			case self::TYPE_SALE:
				$model = new StorageModel\Product\SalePrice( wc_get_product( $id ), new EditValue\Product\SalePrice( $value ) );

				break;
			default:
				return false;
		}

		$result = $model->save();

		if ( $result instanceof WP_Error ) {
			throw new RuntimeException( $result->get_error_message() );
		}

		return true;
	}

}
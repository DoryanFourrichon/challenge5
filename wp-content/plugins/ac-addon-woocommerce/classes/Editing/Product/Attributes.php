<?php

namespace ACA\WC\Editing\Product;

use AC\Request;
use ACP;
use RuntimeException;
use WC_Product_Attribute;

abstract class Attributes implements ACP\Editing\Service {

	/**
	 * @var string
	 */
	protected $attribute;

	/**
	 * @return false|WC_Product_Attribute
	 */
	abstract protected function create_attribute();

	public function __construct( $attribute ) {
		$this->attribute = $attribute;
	}

	public function get_value( $id ) {
		$attribute = $this->get_attribute_object( $id );

		return $attribute ? array_values( $attribute->get_options() ) : [];
	}

	public function get_view( $context ) {
		return new ACP\Editing\View\MultiInput();
	}

	/**
	 * @param int $id
	 *
	 * @return false|WC_Product_Attribute
	 */
	protected function get_attribute_object( $id ) {
		$product = wc_get_product( $id );
		$attributes = $product->get_attributes();

		return isset( $attributes[ $this->attribute ] ) ? $attributes[ $this->attribute ] : false;
	}

	public function update( Request $request ) {
		$attribute = $this->get_attribute_object( $request->get( 'id' ) );

		if ( ! $attribute ) {
			$attribute = $this->create_attribute();
		}

		if ( ! $attribute ) {
			throw new RuntimeException( __( 'Non existing attribute.', 'codepress-admin-columns' ) );
		}

		$attribute->set_options( $request->get( 'value' ) );

		$product = wc_get_product( $request->get( 'id' ) );

		$attributes = $product->get_attributes();
		$attributes[] = $attribute;

		$product->set_attributes( $attributes );

		return $product->save() > 0;
	}

}
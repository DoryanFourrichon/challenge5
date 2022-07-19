<?php

namespace ACA\WC\Editing\Product\Attributes;

use ACA\WC\Editing\Product\Attributes;
use WC_Product_Attribute;

class Custom extends Attributes {

	/**
	 * @var array
	 */
	private $custom_labels;

	public function __construct( $attribute, $custom_labels ) {
		parent::__construct( $attribute );

		$this->custom_labels = $custom_labels;
	}

	protected function create_attribute() {
		$labels = $this->custom_labels;

		if ( ! isset( $labels[ $this->attribute ] ) ) {
			return false;
		}

		$label = $labels[ $this->attribute ];

		$attribute = new WC_Product_Attribute();
		$attribute->set_name( $label );

		return $attribute;
	}

}
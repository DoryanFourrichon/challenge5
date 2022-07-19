<?php

namespace ACA\WC\Column\ShopOrder;

use AC;
use AC\Collection;
use AC\Settings\Column\NumberOfItems;
use AC\Settings\Column\Separator;
use ACA\WC\Filtering;
use ACA\WC\Search;
use ACA\WC\Settings;
use ACA\WC\Sorting;
use ACP;

/**
 * @since 1.3.1
 */
class Product extends AC\Column
	implements ACP\Filtering\Filterable, ACP\Export\Exportable, ACP\Search\Searchable {

	public function __construct() {
		$this->set_group( 'woocommerce' );
		$this->set_type( 'column-wc-product' );
		$this->set_label( __( 'Product', 'woocommerce' ) );
	}

	public function get_raw_value( $order_id ) {
		return new Collection( ac_addon_wc_helper()->get_product_or_variation_ids_by_order( $order_id ) );
	}

	public function get_value( $id ) {
		$values = [];

		foreach ( $this->get_raw_value( $id ) as $product_id ) {
			$values[] = $this->get_formatted_value( $product_id, $product_id );
		}

		$values = array_filter( $values );

		if ( empty( $values ) ) {
			return $this->get_empty_char();
		}

		return ac_helper()->html->more( $values, $this->get_items_limit(), $this->get_separator() );
	}

	/**
	 * @return string
	 */
	public function get_separator() {
		// TODO use new setting in next version
		$setting = $this->get_setting( 'separator' );
		$separator_type = '';

		if ( $setting instanceof Separator ) {
			$separator_type = $setting->get_value();
		}

		switch ( $separator_type ) {
			case 'comma' :
				return ', ';

			case 'newline' :
				return "<br/>";

			case 'none' :
				return '';

			case 'white_space' :
				return '&nbsp;';

			default :
				return parent::get_separator();
		}

	}

	/**
	 * @return int
	 */
	private function get_items_limit() {
		$setting_limit = $this->get_setting( NumberOfItems::NAME );

		return $setting_limit instanceof NumberOfItems
			? $setting_limit->get_number_of_items()
			: 0;
	}

	public function filtering() {
		if ( in_array( $this->get_product_property(), [ Settings\ShopOrder\Product::PROPERTY_TITLE, Settings\ShopOrder\Product::TYPE_SKU ] ) ) {
			return new Filtering\ShopOrder\Product( $this );
		}

		return new ACP\Filtering\Model\Disabled( $this );
	}

	public function export() {
		return new ACP\Export\Model\StrippedValue( $this );
	}

	public function search() {
		return new Search\ShopOrder\Product( $this->get_post_type() );
	}

	public function register_settings() {
		$this->add_setting( new Settings\ShopOrder\Product( $this ) )
		     ->add_setting( new AC\Settings\Column\NumberOfItems( $this ) )
		     ->add_setting( new AC\Settings\Column\Separator( $this ) );
	}

	public function get_product_property() {
		return $this->get_setting( Settings\ShopOrder\Product::NAME )->get_value();
	}

}
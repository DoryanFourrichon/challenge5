<?php

namespace ACA\WC;

use AC;
use AC\Plugin;
use AC\Plugin\Version;
use AC\PluginInformation;
use AC\Registrable;
use ACA\WC\Plugin\SetupFactory;
use ACA\WC\QuickAdd;
use ACP;

final class WooCommerce extends Plugin implements Registrable {

	public function __construct( $file, Version $version ) {
		parent::__construct( $file, $version );
	}

	public function register() {
		ACP\QuickAdd\Model\Factory::add_factory( new QuickAdd\Factory() );

		$plugin_information = new PluginInformation( $this->get_basename() );
		$is_network_active = $plugin_information->is_network_active();
		$setup_factory = new SetupFactory( 'aca_wc_version', $this->get_version() );

		$services = [
			new TableScreen( $this->get_location(), $this->use_product_variations() ),
			new Subscriptions(),
			new Rounding(),
			new Admin( $this->get_location() ),
			new Service\QuickAdd(),
			new Service\Columns(),
			new Service\Table(),
			new Service\ColumnGroups(),
			new Service\ListScreenGroups(),
			new Service\ListScreens( $this->use_product_variations() ),
		];

		$services[] = new AC\Service\Setup( $setup_factory->create( AC\Plugin\SetupFactory::SITE ) );

		if ( $is_network_active ) {
			$services[] = new AC\Service\Setup( $setup_factory->create( AC\Plugin\SetupFactory::NETWORK ) );
		}

		if ( $this->use_product_variations() ) {
			$services[] = new PostType\ProductVariation( $this->get_location() );
		}

		array_map(
			static function ( Registrable $service ) {
				$service->register();
			},
			$services
		);
	}

	/**
	 * @return bool
	 */
	private function use_product_variations() {
		return apply_filters( 'acp/wc/show_product_variations', true ) && $this->is_wc_version_gte( '3.3' );
	}

	/**
	 * @param string $version
	 *
	 * @return bool
	 */
	private function is_wc_version_gte( $version ) {
		return version_compare( WC()->version, (string) $version, '>=' );
	}

}
<?php
/*
Plugin Name: 	    Admin Columns Pro - WooCommerce
Version: 		    3.7.3
Description: 	    Extra columns for the WooCommerce Product, Orders, Customers and Coupon list tables.
Author:             AdminColumns.com
Author URI:         https://www.admincolumns.com
Plugin URI:         https://www.admincolumns.com
Text Domain: 		codepress-admin-columns
WC tested up to:    5.0.0
Requires PHP:       5.6.20
*/

use AC\Plugin\Version;
use ACA\WC\Dependencies;
use ACA\WC\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_admin() ) {
	return;
}

define( 'ACA_WC_VERSION', '3.7.3' );

require_once __DIR__ . '/classes/Dependencies.php';

add_action( 'after_setup_theme', function () {
	$dependencies = new Dependencies( plugin_basename( __FILE__ ), ACA_WC_VERSION );
	$dependencies->requires_acp( '5.7.3' );
	$dependencies->requires_php( '5.6.20' );

	if ( ! class_exists( 'WooCommerce', false ) ) {
		$dependencies->add_missing_plugin( 'WooCommerce', $dependencies->get_search_url( 'WooCommerce' ) );
	}

	if ( $dependencies->has_missing() ) {
		return;
	}

	$class_map = __DIR__ . '/config/autoload-classmap.php';

	if ( is_readable( $class_map ) ) {
		AC\Autoloader::instance()->register_class_map( require $class_map );
	} else {
		AC\Autoloader::instance()->register_prefix( 'ACA\WC', __DIR__ . '/classes' );
	}

	$addon = new WooCommerce( __FILE__, new Version( ACA_WC_VERSION ) );
	$addon->register();
} );

function ac_addon_wc_helper() {
	return new ACA\WC\Helper();
}

function ac_addon_wc() {
	_deprecated_function( __METHOD__, 'NEWVERSION' );

	return new WooCommerce( __FILE__, new Version( ACA_WC_VERSION ) );
}




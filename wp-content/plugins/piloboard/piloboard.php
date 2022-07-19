<?php
/**
 * Plugin Name: Pilo'board
 * Description: Dashboard Client Pilot'in
 * Author: <a href="https://www.pilot-in.com/">Pilot'in</a>
 * Version: 1.3.3
 *
 * @package Pilo'Board
 * @version 1.3.3
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Uninstallation
register_uninstall_hook( __FILE__, 'uninstall_actions' );
function uninstall_actions() {
    delete_option( 'piloboard-licence' );
    delete_option( 'piloboard-client-id' );
    delete_transient( 'piloboard-faq' );
    delete_transient( 'piloboard-forfait' );
    delete_transient( 'piloboard-interlocuteurs' );
}

require_once plugin_dir_path( __FILE__ ) . 'lib/wp-package-updater/class-wp-package-updater.php';

// Enable plugin updates without license check
$piloboard = new WP_Package_Updater(
    'https://piloboard.pilot-in.net',
    wp_normalize_path( __FILE__ ),
    wp_normalize_path( plugin_dir_path( __FILE__ ) )
);

add_action( 'plugins_loaded', 'dummy_plugin_run', 10, 0 );
function dummy_plugin_run() {
}

require_once plugin_dir_path( __FILE__ ) . '/src/class.php';
new PiloBoard();

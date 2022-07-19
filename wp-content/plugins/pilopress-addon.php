<?php
/**
 * Plugin Name:         Pilo'Press - Addon
 * Plugin URI:          https://www.pilot-in.com
 * Description:         Quick start config we use at Pilot'in for WordPress & Pilo'Press
 * Version:             0.2
 * Author:              Pilot'in
 * Author URI:          https://www.pilot-in.com
 * License:             GPLv2 or later
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP:        5.6 or higher
 * Requires at least:   4.9 or higher
 * Text Domain:         pip-addon
 * Domain Path:         /lang
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Addon' ) ) {
    class PIP_Addon {

        // Plugin version
        var $version = '0.2';

        // PiloPress
        var $pip = false;

        /**
         * Pilo'Press - Addon constructor.
         */
        public function __construct() {
            // Do nothing.
        }

        /**
         * Initialize plugin
         */
        public function initialize() {

            // Constants
            $this->define( 'PIP_ADDON_FILE', __FILE__ );
            $this->define( 'PIP_ADDON_PATH', plugin_dir_path( __FILE__ ) );
            $this->define( 'PIP_ADDON_URL', plugin_dir_url( __FILE__ ) );
            $this->define( 'PIP_ADDON_BASENAME', plugin_basename( __FILE__ ) );
            $this->define( 'PIP_THEME_PATH', get_stylesheet_directory() );
            $this->define( 'PIP_THEME_URL', get_stylesheet_directory_uri() );

            // Init
            include_once PIP_ADDON_PATH . 'init.php';

            // Load
            add_action( 'plugins_loaded', array( $this, 'load' ) );

            // Hide login
            pip_addon_include( 'includes/plugins/class-hide-login.php' );

            // Classic Editor
            pip_addon_include( 'includes/plugins/class-classic-editor.php' );
        }

        /**
         * Load classes
         */
        public function load() {

            // Check if Pilo'Press is activated
            if ( !$this->has_pip() ) {
                return;
            }

            // Includes
            add_action( 'acf/init', array( $this, 'acfe_super_dev_mode' ), 5 );
            add_action( 'acf/init', array( $this, 'includes' ) );

        }

        /**
         * Include files
         */
        public function includes() {

            // Fields
            pip_addon_include( 'includes/fields/field-menus.php' );
            pip_addon_include( 'includes/fields/field-menu-items.php' );

            // Field groups
            pip_addon_include( 'includes/field-groups/pip-configuration.php' );
            pip_addon_include( 'includes/field-groups/pip-addon-settings.php' );
            pip_addon_include( 'includes/field-groups/pip-menu-items-icons.php' );
            pip_addon_include( 'includes/field-groups/pip-contact-form.php' );
            pip_addon_include( 'includes/field-groups/pip-term-image.php' );

            // Helpers
            pip_addon_include( 'includes/helpers.php' );

            // Classes
            pip_addon_include( 'includes/plugins/class-bottom-admin-bar.php' );
            pip_addon_include( 'includes/class-main.php' );
            pip_addon_include( 'includes/class-admin.php' );
            pip_addon_include( 'includes/class-menus.php' );
            pip_addon_include( 'includes/class-tailwind.php' );
            pip_addon_include( 'includes/class-cleanup.php' );

        }

        /**
         *  Enable ACFE "Super Dev mode" to have specific features (like show post metas...etc)
         */
        public function acfe_super_dev_mode() {

            $current_user = wp_get_current_user();
            if ( !$current_user ) {
                return;
            }

            /** Check if user logged-in */
            if (
                !is_a( $current_user, 'WP_User' ) ||
                !isset( $current_user->data ) ||
                !isset( $current_user->data->user_login )
            ) {
                return;
            }

            $current_user_login = $current_user->data->user_login ?? '';
            if ( $current_user_login !== 'cabin' ) {
                return;
            }

            define( 'ACFE_SUPER_DEV', true );
        }

        /**
         * Define constants
         *
         * @param      $name
         * @param bool $value
         */
        private function define( $name, $value = true ) {
            if ( !defined( $name ) ) {
                define( $name, $value );
            }
        }

        /**
         * Check if Pilo'Press is activated
         *
         * @return bool
         */
        public function has_pip() {

            // If Pilo'Press already available, return
            if ( $this->pip ) {
                return true;
            }

            $pip_exists = class_exists( 'PiloPress' );
            if ( !$pip_exists ) {
                return false;
            }

            $pip_instance = new PiloPress();
            if ( !$pip_instance ) {
                return false;
            }

            $acf = $pip_instance->has_acf();
            if ( !$acf ) {
                return false;
            }

            // Check if Pilo'Press activated
            $this->pip = true;

            return $this->pip;
        }

    }
}

/**
 * Instantiate Pilo'Press - Pilot'in Addon
 *
 * @return PIP_Addon
 */
function pip_addon() {
    global $pip_addon;

    if ( !isset( $pip_addon ) ) {
        $pip_addon = new PIP_Addon();
        $pip_addon->initialize();
    }

    return $pip_addon;
}

// Instantiate
pip_addon();

// .htaccess optmization for performances & security
function htaccess_optimization( $htaccess_content = '' ) {

    // Need to require this file to use markers functions
    require_once ABSPATH . 'wp-admin/includes/misc.php';

    // Get path to main .htaccess for WordPress
    $htaccess = ABSPATH . '.htaccess';

    // If "PilotIn" markers are already there, no need to update
    $htaccess_already_optimized = extract_from_markers( $htaccess, 'PilotIn' );
    if ( $htaccess_already_optimized ) {
        return $htaccess_content;
    }

    $lines = array(
        '',
        '# Security headers',
        '<IfModule mod_headers.c>',
        'Header unset Server',
        'Header unset X-Powered-By',
        'Header always set Strict-Transport-Security: "max-age=31536000" env=HTTPS',
        'Header always set Content-Security-Policy "upgrade-insecure-requests"',
        'Header always set X-Content-Type-Options "nosniff"',
        'Header always set X-XSS-Protection "1; mode=block"',
        'Header always set Expect-CT "max-age=7776000, enforce"',
        'Header always set Referrer-Policy: "no-referrer-when-downgrade"',
        '</IfModule>',
        '',
        '# Disable directory browsing',
        'Options All -Indexes',
        '',
        '# Deny access to all .htaccess files',
        '<files ~ "^.*\.([Hh][Tt][Aa])">',
        'order allow,deny',
        'deny from all',
        'satisfy all',
        '</files>',
        '',
        '# Deny access to readme.html',
        '<files readme.html>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to readme.txt',
        '<files readme.txt>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to license.txt',
        '<files license.txt>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to wp-config.php file',
        '<files wp-config.php>',
        'order allow,deny',
        'deny from all',
        '</files>',
        '',
        '# Deny access to wp-config-sample.php file',
        '<files wp-config-sample.php>',
        'order allow,deny',
        'deny from all',
        '</files>',
        '',
        '# Deny access to debug.log',
        '<files debug.log>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to .user.ini',
        '<files .user.ini>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to error_log',
        '<files error_log>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to package.json',
        '<files package.json>',
        'Order allow,deny',
        'Deny from all',
        '</files>',
        '',
        '# Deny access to wp-includes folder and files',
        '<IfModule mod_rewrite.c>',
        'RewriteEngine On',
        'RewriteBase /',
        'RewriteRule ^wp-admin/includes/ - [F,L]',
        'RewriteRule !^wp-includes/ - [S=3]',
        'RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]',
        'RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]',
        'RewriteRule ^wp-includes/theme-compat/ - [F,L]',
        '</IfModule>',
        '',
    );

    // If we have access to htaccess content (plugin compatibility: WP Rocket...etc)
    if ( $htaccess_content ) {

        $lines_strings = implode( PHP_EOL, $lines );

        $htaccess_content .= PHP_EOL; // Jump a line
        $htaccess_content .= '# BEGIN PilotIn'; // Start marker
        $htaccess_content .= $lines_strings; // Optimization content
        $htaccess_content .= '# END PilotIn'; // End marker
        $htaccess_content .= PHP_EOL; // Jump a line

        return $htaccess_content;
    }

    // Add htaccess optimized content with "PilotIn" markers
    insert_with_markers( $htaccess, 'PilotIn', $lines );

}

// Update .htaccess file when WP Rocket updates it
// FIXME: Sometimes this code seems to corrupt .htaccess file (not managed to find exactly why)
// add_filter( 'rocket_htaccess_marker', 'htaccess_optimization', 20 );

/**
 *  On plugin activation
 *  (useful to run code only once)
 */
register_activation_hook( __FILE__, 'pip_addon_activation' );
function pip_addon_activation( $network_wide ) {

    /**
     *  "ACF Font Awesome" plugin
     *  - Update configuration
     */
    update_option( 'ACFFA_active_icon_set', 'pro' );
    $acffa_settings                  = get_option( 'acffa_settings' ) ?: array(); // phpcs:ignore
    $acffa_settings['acffa_pro_cdn'] = true;
    update_option( 'acffa_settings', $acffa_settings );

    /**
     *  "ACF Font Awesome" plugin
     *  - Force refresh of icons (to have pro icons)
     */
    if ( !defined( 'ACFFA_FORCE_REFRESH' ) ) {
        define( 'ACFFA_FORCE_REFRESH', true );
        do_action( 'ACFFA_refresh_latest_icons' ); // phpcs:ignore
    }

    /**
     *  "Duplicate Post" plugin
     *  - Set only "clone" quick action + remove welcome notice
     */
    $dp_show_link                      = get_option( 'duplicate_post_show_link' ) ?: array(); // phpcs:ignore
    $dp_show_link['new_draft']         = 0;
    $dp_show_link['rewrite_republish'] = 0;
    $dp_show_link['clone']             = 1;
    update_option( 'duplicate_post_show_link', $dp_show_link );
    update_option( 'duplicate_post_show_notice', 0 );

    // htaccess fixes to optimize performances & security
    htaccess_optimization();

    /**
     *  "Yoast SEO" plugin
     *  - Disable "author" archives by default
     */
    $yoast_options                         = get_option( 'wpseo_titles' ) ?: array(); // phpcs:ignore
    $yoast_options['disable-author']       = 1;
    $yoast_options['noindex-author-wpseo'] = 1;
    update_option( 'wpseo_titles', $yoast_options );

    /**
     *  ACFE Form - Import default form
     */
    // FIXME: Move this piece of code below in a safer hook which have ACF & ACFE enabled, ideally it should be executed once.
    // if ( !function_exists( 'import_acfe_contact_form' ) ) {
    //     function import_acfe_contact_form() {

    //         // Do this only if PiloPress addon & ACF are available
    //         if ( !defined( 'PIP_ADDON_PATH' ) || !function_exists( 'acf' ) ) {
    //             return;
    //         }

    //         // Get exported contact form
    //         $default_form_json = file_get_contents( PIP_ADDON_PATH . 'includes/forms/default-contact-form.json' ); // phpcs:ignore
    //         if ( !$default_form_json ) {
    //             return;
    //         }

    //         // Decode json
    //         $default_form_data = json_decode( $default_form_json, true );

    //         // Force initialize of ACF tools (only loaded on Tools page by default)
    //         acf()->admin_tools = new acf_admin_tools();
    //         acf()->admin_tools->load();

    //         // Initialise ACFE Tool Import Form & import contact form
    //         $acfe_tool_import_form = new ACFE_Admin_Tool_Import_Form();
    //         $acfe_tool_import_form->import_external( $default_form_data );

    //     }
    // }

    // import_acfe_contact_form();

}

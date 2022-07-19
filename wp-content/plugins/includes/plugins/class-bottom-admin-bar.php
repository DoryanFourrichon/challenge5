<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Addon_Bottom_Admin_Bar' ) ) {

    /**
     * Class PIP_Addon_Bottom_Admin_Bar
     */
    class PIP_Addon_Bottom_Admin_Bar {

        /**
         * PIP_Addon_Bottom_Admin_Bar constructor.
         */
        public function __construct() {
            add_action( 'after_setup_theme', array( &$this, 'show_toolbar_check' ) );
            add_action( 'plugins_loaded', array( &$this, 'myplugin_init' ) );
            add_action( 'wp_enqueue_scripts', array( &$this, 'admin_bar_script_init' ), 11 );
            add_action( 'get_header', array( &$this, 'remove_admin_bar_css' ) );
            add_action( 'wp_head', array( &$this, 'my_admin_bar_bump_cb' ) );
            add_action( 'wp_footer', array( &$this, 'keyboard_shortcut' ), 21 );
        }

        /**
         * Checking the 'Show Toolbar when viewing site' check box.
         */
        public function show_toolbar_check() {
            wp_get_current_user();
            if ( 'true' !== get_user_meta( get_current_user_id(), 'show_admin_bar_front', 1 ) ) {
                return;
            }
        }

        /**
         * Load plugin textdomain
         */
        public function myplugin_init() {
            load_plugin_textdomain( 'bottom-admin-bar', false, dirname( plugin_basename( __FILE__ ) ) );
        }

        /**
         * Override default admin bar CSS.
         */
        public function admin_bar_script_init() {
            if ( is_user_logged_in() ) {
                wp_register_style( 'adminBarStyleSheet', PIP_ADDON_URL . 'assets/css/view.css' );
                wp_enqueue_style( 'adminBarStyleSheet' );
                wp_enqueue_script( 'jquery' );
            }
        }

        /**
         * Remove default admin bar inline CSS
         */
        public function remove_admin_bar_css() {
            remove_action( 'wp_head', '_admin_bar_bump_cb' );
        }

        /**
         * Rewrite admin bar inline CSS
         */
        public function my_admin_bar_bump_cb() {
            $output = '<style type="text/css" media="screen">';
            $output .= 'html { padding-bottom: 32px !important; }
            * html body { padding-bottom: 32px !important; }
            @media screen and ( max-width: 782px ) {
            html { padding-bottom: 46px !important; }
            * html body { padding-bottom: 46px !important; }
            }
            html.spaceClear { padding-bottom: 0 !important; }
            html.spaceClear body { padding-bottom: 0 !important;}';

            // Delete Twenty Sixteen head spacing
            if ( get_option( 'template' ) === 'twentysixteen' ) {
                $output .= '.admin-bar:before {
                    top: 0 !important;
                }';
            }

            $output .= '</style>';
            if ( is_user_logged_in() ) {
                echo $output;
            }
        }

        /**
         * Add keyboard shortcut
         */
        public function keyboard_shortcut() {
            $output = "<script type=\"text/javascript\">
            jQuery( document ).ready( function( $ ){
                $( 'body' ).keydown(function( event ){
                    if( event.shiftKey === true && event.which === 65 ){
                        $( '#wpadminbar' ).slideToggle( 'fast' );
                        $( 'html' ).toggleClass( 'spaceClear' );
                    }
                });
            });
            </script>";

            if ( is_user_logged_in() ) {
                echo $output;
            }
        }
    }

    // Instantiate
    new PIP_Addon_Bottom_Admin_Bar();
}

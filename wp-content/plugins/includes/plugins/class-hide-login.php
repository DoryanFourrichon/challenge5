<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Addon_Hide_Login' ) ) {

    /**
     * Class PIP_Addon_Hide_Login
     */
    class PIP_Addon_Hide_Login {

        /**
         * WP Login PHP
         *
         * @var $wp_login_php
         */
        private $wp_login_php;

        /**
         * PIP_Addon_Hide_Login constructor.
         */
        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );
            add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
            add_action( 'setup_theme', array( $this, 'setup_theme' ), 1 );

            add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
            add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
            add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
            add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );

            remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

            add_action( 'template_redirect', array( $this, 'redirect_export_data' ) );
            add_filter( 'login_url', array( $this, 'login_url' ), 10, 3 );

            add_filter( 'user_request_action_email_content', array( $this, 'user_request_action_email_content' ), 999, 2 );
            add_filter( 'rocket_cache_reject_uri', array( $this, 'wp_rocket_no_cache_on_login_page' ) );
        }

        public function wp_rocket_no_cache_on_login_page( $urls ) {
            $urls[] = '/connect-in/';
            return $urls;
        }

        /**
         * Replace wp-login.php
         *
         * @param $email_text
         * @param $email_data
         *
         * @return string|string[]
         */
        public function user_request_action_email_content( $email_text, $email_data ) {
            $email_text = str_replace( '###CONFIRM_URL###', esc_url_raw( str_replace( $this->new_login_slug() . '/', 'wp-login.php', $email_data['confirm_url'] ) ), $email_text );

            return $email_text;
        }

        /**
         * Need slash or not
         *
         * @return bool
         */
        private function use_trailing_slashes() {

            return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );

        }

        /**
         * Maybe add slash
         *
         * @param $string
         *
         * @return string
         */
        private function user_trailingslashit( $string ) {

            return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );

        }

        /**
         * Load WP
         */
        private function wp_template_loader() {
            global $pagenow;
            $pagenow = 'index.php';

            if ( !defined( 'WP_USE_THEMES' ) ) {
                define( 'WP_USE_THEMES', true );
            }

            wp();

            require_once ABSPATH . WPINC . '/template-loader.php';

            die;
        }

        /**
         * Login slug
         *
         * @return string
         */
        private function new_login_slug() {
            return 'connect-in';
        }

        /**
         * Login URL
         *
         * @param null $scheme
         *
         * @return string
         */
        public function new_login_url( $scheme = null ) {

            $url = home_url( '/', $scheme );

            if ( get_option( 'permalink_structure' ) ) {
                return $this->user_trailingslashit( $url . $this->new_login_slug() );
            } else {
                return $url . '?' . $this->new_login_slug();
            }

        }

        /**
         * Redirect URL
         *
         * @param null $scheme
         *
         * @return string|void
         */
        public function new_redirect_url( $scheme = null ) {
            return home_url();
        }

        /**
         * Redirect export data
         */
        public function redirect_export_data() {
            if ( !empty( $_GET ) && isset( $_GET['action'] ) && 'confirmaction' === $_GET['action'] && isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
                $request_id = (int) $_GET['request_id'];
                $key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
                $result     = wp_validate_user_request_key( $request_id, $key );
                if ( !is_wp_error( $result ) ) {
                    wp_redirect(
                        add_query_arg(
                            array(
                                'action'      => 'confirmaction',
                                'request_id'  => $_GET['request_id'],
                                'confirm_key' => $_GET['confirm_key'],
                            ),
                            $this->new_login_url()
                        )
                    );
                    exit();
                }
            }
        }

        /**
         * Plugins loaded
         */
        public function plugins_loaded() {

            global $pagenow;

            if ( !is_multisite()
                 && ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-signup' ) !== false
                      || strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-activate' ) !== false ) ) {

                wp_die( __( 'This feature is not enabled.', 'pip-addon' ) );

            }

            $request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

            if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
                   || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
                 && !is_admin() ) {

                $this->wp_login_php = true;

                $_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

                $pagenow = 'index.php';

            } elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) )
                       || ( !get_option( 'permalink_structure' )
                            && isset( $_GET[ $this->new_login_slug() ] )
                            && empty( $_GET[ $this->new_login_slug() ] ) ) ) {

                $pagenow = 'wp-login.php';

            } elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
                         || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
                       && !is_admin() ) {

                $this->wp_login_php = true;

                $_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

                $pagenow = 'index.php';
            }

        }

        /**
         * Setup theme
         */
        public function setup_theme() {
            global $pagenow;

            if ( !is_user_logged_in() && 'customize.php' === $pagenow ) {
                wp_die( __( 'This has been disabled', 'pip-addon' ), 403 );
            }
        }

        /**
         * WP Loaded
         */
        public function wp_loaded() {

            global $pagenow;

            $request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

            if ( !isset( $_POST['post_password'] ) ) {

                if ( is_admin() && !is_user_logged_in() && !defined( 'DOING_AJAX' ) && $pagenow !== 'admin-post.php' && $request['path'] !== '/wp-admin/options.php' ) {
                    wp_safe_redirect( $this->new_redirect_url() );
                    die();
                }

                if ( $pagenow === 'wp-login.php'
                     && $request['path'] !== $this->user_trailingslashit( $request['path'] )
                     && get_option( 'permalink_structure' ) ) {

                    wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() )
                                      . ( !empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

                    die;

                } elseif ( $this->wp_login_php ) {

                    if ( ( $referer = wp_get_referer() )
                         && strpos( $referer, 'wp-activate.php' ) !== false
                         && ( $referer = parse_url( $referer ) )
                         && !empty( $referer['query'] ) ) {

                        parse_str( $referer['query'], $referer );

                        @require_once WPINC . '/ms-functions.php';

                        if ( !empty( $referer['key'] )
                             && ( $result = wpmu_activate_signup( $referer['key'] ) )
                             && is_wp_error( $result )
                             && ( $result->get_error_code() === 'already_active'
                                  || $result->get_error_code() === 'blog_taken' ) ) {

                            wp_safe_redirect( $this->new_login_url()
                                              . ( !empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

                            die;

                        }

                    }

                    $this->wp_template_loader();

                } elseif ( $pagenow === 'wp-login.php' ) {
                    $redirect_to = admin_url();

                    $requested_redirect_to = '';
                    if ( isset( $_REQUEST['redirect_to'] ) ) {
                        $requested_redirect_to = $_REQUEST['redirect_to'];
                    }

                    if ( is_user_logged_in() ) {
                        $user = wp_get_current_user();
                        if ( !isset( $_REQUEST['action'] ) ) {
                            wp_safe_redirect( $redirect_to );
                            die();
                        }
                    }

                    @require_once ABSPATH . 'wp-login.php';

                    die;

                }

            }

        }

        /**
         * Site URL
         *
         * @param $url
         * @param $path
         * @param $scheme
         * @param $blog_id
         *
         * @return string
         */
        public function site_url( $url, $path, $scheme, $blog_id ) {
            return $this->filter_wp_login_php( $url, $scheme );
        }

        /**
         * Network site URL
         *
         * @param $url
         * @param $path
         * @param $scheme
         *
         * @return string
         */
        public function network_site_url( $url, $path, $scheme ) {
            return $this->filter_wp_login_php( $url, $scheme );
        }

        /**
         * WP Redirect
         *
         * @param $location
         * @param $status
         *
         * @return string
         */
        public function wp_redirect( $location, $status ) {

            if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
                return $location;
            }

            return $this->filter_wp_login_php( $location );

        }

        /**
         * Change login URL
         *
         * @param      $url
         * @param null $scheme
         *
         * @return string
         */
        public function filter_wp_login_php( $url, $scheme = null ) {

            if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
                return $url;
            }

            if ( strpos( $url, 'wp-login.php' ) !== false && strpos( wp_get_referer(), 'wp-login.php' ) === false ) {

                if ( is_ssl() ) {

                    $scheme = 'https';

                }

                $args = explode( '?', $url );

                if ( isset( $args[1] ) ) {

                    parse_str( $args[1], $args );

                    if ( isset( $args['login'] ) ) {
                        $args['login'] = rawurlencode( $args['login'] );
                    }

                    $url = add_query_arg( $args, $this->new_login_url( $scheme ) );

                } else {

                    $url = $this->new_login_url( $scheme );

                }

            }

            return $url;

        }

        /**
         * Replace wp-login.php
         *
         * @param $value
         *
         * @return string|string[]
         */
        public function welcome_email( $value ) {

            return $value = str_replace( 'wp-login.php', trailingslashit( 'connect-in' ), $value );

        }

        /**
         * Change login URL
         *
         * @param $login_url
         * @param $redirect
         * @param $force_re_auth
         *
         * @return string|void
         */
        public function login_url( $login_url, $redirect, $force_re_auth ) {
            if ( is_404() ) {
                return '#';
            }

            if ( $force_re_auth === false ) {
                return $login_url;
            }

            if ( empty( $redirect ) ) {
                return $login_url;
            }

            $redirect = explode( '?', $redirect );

            if ( $redirect[0] === admin_url( 'options.php' ) ) {
                $login_url = admin_url();
            }

            return $login_url;
        }
    }

    // Instantiate
    new PIP_Addon_Hide_Login();
}

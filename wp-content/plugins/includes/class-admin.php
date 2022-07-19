<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Addon_Admin' ) ) {

    /**
     * Class PIP_Addon_Admin
     */
    class PIP_Addon_Admin {

        /**
         * PIP_Addon_Admin constructor.
         */
        public function __construct() {

            // WP hooks
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
            //add_action( 'admin_print_scripts', array( $this, 'remove_admin_notices' ) );
            add_action( 'admin_init', array( $this, 'customize_admin' ) );
            add_action( 'admin_init', array( $this, 'native_notices_in_pip_admin_pages' ) );
            add_action( 'login_enqueue_scripts', array( $this, 'login_logo_style' ) );
            add_filter( 'login_headerurl', array( $this, 'login_header_url' ) );
            add_filter( 'login_headertext', array( $this, 'login_header_title' ) );
            add_filter( 'auth_cookie_expiration', array( $this, 'auth_cookie_extend_expiration' ), 10, 3 );

            // ACF hooks
            add_action( 'acf/save_post', array( $this, 'clear_content_meta' ), 5 );
            add_filter( 'acf/update_value/type=wysiwyg', array( $this, 'fill_content_meta' ), 20, 3 );
            add_action( 'acf/save_post', array( $this, 'update_post_content' ), 15 );
            add_action( 'acf/save_post', array( $this, 'update_reading_time' ), 20 );

            // 3rd party hooks
            add_filter( 'ptags/option', array( $this, 'prefill_plugin_tags' ) );
            add_filter( 'duplicate_post_enabled_post_types', array( $this, 'public_posts_can_be_cloned' ) );

        }

        // WPS Notice Center - Native notices on Pilo'Press admin pages
        public function native_notices_in_pip_admin_pages() {

            // Check if WPS Notice Center plugin is loaded
            if ( !defined( 'WPS_NOTICE_CENTER_DIR' ) ) {
                return;
            }

            $pip_admin = acf_get_instance( 'PIP_Admin' );
            if ( !$pip_admin ) {
                return;
            }

            $pip_admin_pages    = $pip_admin->get_style_admin_pages();
            $current_admin_page = acf_maybe_get_GET( 'page' );
            if ( in_array( $current_admin_page, $pip_admin_pages, true ) ) {

                // Load class instance so we can remove_action from inside the class
                $wnc_plugin_instance = \WPS\WPS_Notice_Center\Plugin::get_instance();
                remove_action( 'admin_footer', array( $wnc_plugin_instance, 'admin_footer' ), 9999 );

            }

        }

        // Posts from public post types can be cloned by default
        public function public_posts_can_be_cloned( $enabled_post_types ) {

            $public_post_types = get_post_types(
                array(
                    'public' => 1,
                )
            );

            // Filter unwanted post-types
            unset( $public_post_types['attachment'] );

            $public_post_types  = array_values( $public_post_types );
            $enabled_post_types = array_unique( array_merge( $enabled_post_types, $public_post_types ) );

            return $enabled_post_types;
        }

        // Prefill plugins tags
        public function prefill_plugin_tags( $option ) {

            // Get current plugins & tags data
            $plugins = isset( $option['plugins'] ) ? $option['plugins'] : array();
            $tags    = isset( $option['tags'] ) ? $option['tags'] : array();

            // acf_log( 'DEBUG: $plugins', $plugins );

            // Edit plugins data
            $plugins = wp_parse_args(
                $plugins,
                array(
                    'bottom-admin-toolbar'                 => array( // Plugin slug
                        'tag'   => __( 'Gestion de l\'administration' ), // Tag text displayed next to the plugin version
                        'color' => 2, // User preference schematic colors, from 1 to 4+
                    ),

                    'piloboard'                            => array( // Plugin slug
                        'tag'   => __( 'Gestion de l\'administration' ), // Tag text displayed next to the plugin version
                        'color' => 2, // User preference schematic colors, from 1 to 4+
                    ),

                    'acf-extended'                         => array( // Plugin slug
                        'tag'   => __( 'ACF' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'acf-extended-pro'                     => array( // Plugin slug
                        'tag'   => __( 'ACF' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'advanced-custom-fields'               => array( // Plugin slug
                        'tag'   => __( 'ACF' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'advanced-custom-fields-font-awesome'  => array( // Plugin slug
                        'tag'   => __( 'ACF' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'advanced-custom-fields-pro'           => array( // Plugin slug
                        'tag'   => __( 'ACF' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'duplicator'                           => array( // Plugin slug
                        'tag'   => __( 'Sauvegarde' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'duplicator-pro'                       => array( // Plugin slug
                        'tag'   => __( 'Sauvegarde' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'wp-mail-smtp'                         => array( // Plugin slug
                        'tag'   => __( 'Mails' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'really-simple-ssl'                    => array( // Plugin slug
                        'tag'   => __( 'HTTPS' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'query-monitor'                        => array( // Plugin slug
                        'tag'   => __( 'Outil dév' ), // Tag text displayed next to the plugin version
                        'color' => 2, // User preference schematic colors, from 1 to 4+
                    ),

                    'complianz-gdpr'                       => array( // Plugin slug
                        'tag'   => __( 'RGPD' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'pilopress'                            => array( // Plugin slug
                        'tag'   => __( 'Pagebuilder' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'pilopress-addon'                      => array( // Plugin slug
                        'tag'   => __( 'Pagebuilder' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'acf-content-analysis-for-yoast-seo'   => array( // Plugin slug
                        'tag'   => __( 'SEO' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'wordpress-seo'                        => array( // Plugin slug
                        'tag'   => __( 'SEO' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'wp-404-auto-redirect-to-similar-post' => array( // Plugin slug
                        'tag'   => __( 'SEO' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'worker'                               => array( // Plugin slug
                        'tag'   => __( 'Maintenance' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'plugin-tags'                          => array( // Plugin slug
                        'tag'   => __( 'Gestion des plugins' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'duplicate-post'                       => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    'admin-columns-pro'                    => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    'admin-columns-pro-advanced-custom-fields-acf' => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    // 'ac-addon-woocommerce'                 => array( // Plugin slug
                    'admin-columns-pro-woocommerce'        => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    'taxonomy-terms-order'                 => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    'post-types-order'                     => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    'regenerate-thumbnails'                => array( // Plugin slug
                        'tag'   => __( 'Gestion du contenu' ), // Tag text displayed next to the plugin version
                        'color' => 3, // User preference schematic colors, from 1 to 4+
                    ),

                    'imagify'                              => array( // Plugin slug
                        'tag'   => __( 'Optimisation performances' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'wp-rocket'                            => array( // Plugin slug
                        'tag'   => __( 'Optimisation performances' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'litespeed-cache'                      => array( // Plugin slug
                        'tag'   => __( 'Optimisation performances' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'woocommerce'                          => array( // Plugin slug
                        'tag'   => __( 'E-commerce' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'wordfence'                            => array( // Plugin slug
                        'tag'   => __( 'Sécurité' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),

                    'safe-svg'                             => array( // Plugin slug
                        'tag'   => __( 'Sécurité' ), // Tag text displayed next to the plugin version
                        'color' => 1, // User preference schematic colors, from 1 to 4+
                    ),
                )
            );

            // Edit tags data
            $tags = wp_parse_args(
                $tags,
                array(

                    // Filter text (should be same tag text as above)
                    // 'To delete' => array(
                    //     'view' => 1, // Boolean setting to display filter above plugins list
                    // ),

                    // ... add more by duplicating lines above

                )
            );

            // We merge it with current data
            $new_option = wp_parse_args(
                array(
                    'plugins' => $plugins,
                    'tags'    => $tags,
                ),
                $option
            );

            // Return the new option
            return $new_option;
        }

        /**
         * Load admin assets
         */
        public function admin_assets() {
            wp_enqueue_script(
                'pip-addon-layouts',
                PIP_ADDON_URL . 'assets/js/pip-addon-layouts.js',
                array( 'jquery' ),
                1.0,
                true
            );
            wp_enqueue_style(
                'pip-addon-layouts',
                PIP_ADDON_URL . 'assets/css/admin-layouts.css',
                null,
                1.0
            );
        }

        /**
         *  WordPress - Admin
         *  - Hide Admin notices mess
         */
        public function remove_admin_notices() {
            global $wp_filter;
            if ( is_user_admin() ) {
                if ( isset( $wp_filter['user_admin_notices'] ) ) {
                    unset( $wp_filter['user_admin_notices'] );
                }
            } elseif ( isset( $wp_filter['admin_notices'] ) ) {
                unset( $wp_filter['admin_notices'] );
            }

            if ( isset( $wp_filter['all_admin_notices'] ) && apply_filters( 'pip_remove_all_admin_notices', true ) ) {
                unset( $wp_filter['all_admin_notices'] );
            }
        }

        /**
         *  Extend "cabin" logged in duration
         *
         * @param $expiration
         * @param $user_id
         * @param $remember
         *
         * @return float|int
         */
        public function auth_cookie_extend_expiration( $expiration, $user_id, $remember ) {

            // Get current user object
            $current_user = get_user_by( 'ID', $user_id );
            if ( !$current_user ) {
                return $expiration;
            }

            // Check if it's "cabin"
            $current_user_login = $current_user->data->user_login ?? '';
            if ( $current_user_login !== 'cabin' ) {
                return $expiration;
            }

            // Stay logged for a year
            return YEAR_IN_SECONDS;
        }

        /**
         * Change login logo
         */
        public function login_logo_style() {
            $logo_id = get_theme_mod( 'custom_logo' );
            $logo    = wp_get_attachment_image_src( $logo_id, 'full' );

            if ( $logo ) : ?>
                <style type="text/css">
                    #login h1 a, .login h1 a {
                        background-image: url('<?php echo reset( $logo ); ?>');
                        height: 80px;
                        width: 320px;
                        background-repeat: no-repeat;
                        background-size: contain;
                    }
                </style>
                <?php
            endif;
        }

        /**
         * Change login URL
         *
         * @return string|void
         */
        public function login_header_url() {
            return home_url();
        }

        /**
         * Change login title
         *
         * @return string|void
         */
        public function login_header_title() {
            return get_bloginfo( 'name' );
        }

        /**
         * Customize admin
         */
        public function customize_admin() {
            // Yoast not activated
            if ( !class_exists( 'WPSEO_Post_Type' ) ) {
                return;
            }

            // Get all post types
            $post_types = WPSEO_Post_Type::get_accessible_post_types();

            // If no post types, return
            if ( !is_array( $post_types ) || $post_types === array() ) {
                return;
            }

            // Instantiate PIP_Addon_Main to able to have access to the function column_hidden
            $addon_main = new PIP_Addon_Main();

            // Browse post types
            foreach ( $post_types as $post_type ) {
                $filter = sprintf( 'get_user_option_%s', sprintf( 'manage%scolumnshidden', 'edit-' . $post_type ) );
                add_filter( $filter, array( $addon_main, 'column_hidden' ), 10, 3 );
            }
        }

        public function update_reading_time( $post_id ) {

            // Don't update while doing autosave
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            // Don't update if is post revision
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }

            // Don't update non-viewable post-types
            $post_type = get_post_type( $post_id );
            if ( !is_post_type_viewable( $post_type ) ) {
                return;
            }

            // Get post data
            $post = get_post( $post_id );
            if ( !$post || !is_a( $post, 'WP_Post' ) ) {
                return;
            }

            $post_content_stripped = wp_strip_all_tags( $post->post_content, true );
            if ( !$post_content_stripped ) {
                return;
            }

            // Get word numbers
            $word_count = str_word_count( $post_content_stripped );

            // We assume we read 250 words per minute
            $minutes = ceil( $word_count / 250 );

            // Save the post meta
            update_post_meta( $post_id, 'reading_time', $minutes );

        }

        public function clear_content_meta( $post_id ) {

            // Don't update while doing autosave
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            // Don't update if is post revision
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }

            // Don't update non-viewable post-types
            $post_type = get_post_type( $post_id );
            if ( !is_post_type_viewable( $post_type ) ) {
                return;
            }

            // Reset "content_meta" value
            update_post_meta( $post_id, 'content_meta', '' );

        }

        public function update_post_content( $post_id ) {

            // Don't update while doing autosave
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            // Don't update if is post revision
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }

            // Don't update non-viewable post-types
            $post_type = get_post_type( $post_id );
            if ( !is_post_type_viewable( $post_type ) ) {
                return;
            }

            // Allow filter for 3rd party to prevent erasing potential existing post_content for specific post-types
            $blacklisted_post_types = apply_filters( 'pip_addon/auto_post_content/blacklist_post_types', array( 'product' ) );
            if ( in_array( $post_type, $blacklisted_post_types, true ) ) {
                return;
            }

            // Update only if there is a content_meta value
            $content_meta = get_post_meta( $post_id, 'content_meta', true );
            if ( !$content_meta ) {
                return;
            }

            // Update only if Pilo'Press is present
            $has_pip_flexible = get_post_meta( $post_id, 'pip_flexible', true );
            if ( !$has_pip_flexible ) {

                // Check for ACF Single meta aswell just incase
                $has_pip_flexible = get_post_meta( $post_id, 'acf' );
                if ( empty( $has_pip_flexible ) ) {
                    return;
                }

            }

            // Update post_content
            $post_update = wp_update_post(
                array(
                    'ID'           => $post_id,
                    'post_content' => $content_meta,
                )
            );

        }

        public function fill_content_meta( $value, $post_id, $field ) {

            if ( !$value ) {
                return $value;
            }

            // Don't update while doing autosave
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $value;
            }

            // Don't update if is post revision
            if ( wp_is_post_revision( $post_id ) ) {
                return $value;
            }

            // Don't update non-viewable post-types
            $post_type = get_post_type( $post_id );
            if ( !is_post_type_viewable( $post_type ) ) {
                return $value;
            }

            // Increment content_meta value with others values
            $content_meta = get_post_meta( $post_id, 'content_meta', true );
            $content_meta = "$content_meta $value";
            update_post_meta( $post_id, 'content_meta', $content_meta );

            return $value;
        }

    }

    // Instantiate
    new PIP_Addon_Admin();
}

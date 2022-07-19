<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Addon_Main' ) ) {

    /**
     * Class PIP_Addon_Main
     */
    class PIP_Addon_Main {

        /**
         * PIP_Addon_Main constructor.
         */
        public function __construct() {

            // WP hooks
            add_action( 'init', array( $this, 'init_hook' ) );
            add_action( 'init', array( $this, 'pip_update_gdpr_content' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_assets' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
            add_action( 'wp_head', array( $this, 'enqueue_gtm' ) );
            add_action( 'wp_body_open', array( $this, 'enqueue_gtm_noscript' ) );
            add_action( 'sanitize_file_name', array( $this, 'sanitize_file_name' ) );
            add_action( 'upload_mimes', array( $this, 'upload_mime_types' ) );
            add_filter( 'ACFFA_get_fa_url', array( $this, 'dequeue_font_awesome_free' ) );
            add_action( 'wp_head', array( $this, 'enqueue_font_awesome_pro' ), 1 );
            add_action( 'customize_register', array( $this, 'pip_add_logo_versions_to_customizer' ) );
            add_filter( 'template_include', array( $this, 'pip_addon_templates' ), 20 );
            add_filter( 'option_image_default_link_type', array( $this, 'attachment_media_url_by_default' ), 99 );
            add_filter( 'do_shortcode_tag', array( $this, 'gallery_lightbox' ), 10, 4 );
            add_shortcode( 'pip_icon_fa', array( $this, 'shortcode_icon_fa' ) );
            add_filter( 'robots_txt', array( $this, 'update_robots_txt_content' ), 10, 2 );

            // WC hooks
            add_filter( 'woocommerce_locate_template', array( $this, 'wc_template_path' ), 99, 3 );

            // ACF hooks
            add_filter( 'acf/get_field_group_style', array( $this, 'pip_display_wysiwyg_on_product' ), 20, 2 );
            add_filter( 'acf/fields/google_map/api', array( $this, 'acf_register_map_api' ) );
            add_filter( 'acf/render_field_settings/type=pip_font_color', array( $this, 'pip_font_color_settings' ), 20, 1 );
            add_filter( 'acf/format_value/type=pip_font_color', array( $this, 'pip_font_color_format_value' ), 20, 3 );
            add_filter( 'acf/load_field_groups', array( $this, 'pip_flexible_layouts_locations' ), 30 );

            // ACFE hooks
            acfe_update_setting( 'modules/single_meta', true );

            // PIP hooks
            add_filter( 'pip/builder/parameters', array( $this, 'pip_flexible_args' ) );
            add_filter( 'pip/builder/locations', array( $this, 'pip_flexible_locations' ) );

        }

        /**
         *  Add robots.txt rules to prevent SERP issues
         */
        public function update_robots_txt_content( $output, $public ) {

            // Only do this when the site must be indexed
            if ( $public == '1' ) {

                // Get site path
                $site_url = wp_parse_url( site_url() );
                $path     = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';

                // Prevent some sensible paths from being indexed
                $output .= "Disallow: $path/author/*\n"; // Author pages
                $output .= "Disallow: $path/?s=\n"; // Search page
                $output .= "Disallow: $path/search\n"; // Search page
                $output .= "Disallow: /*?*\n"; // Query param
                $output .= "Disallow: /*?\n"; // Query param

                // Prevent some sensible file types to be indexed
                foreach ( array( 'pdf', 'woff', 'woff2', 'ttf', 'otf', 'eot', 'zip', 'gz' ) as $ext ) {
                    $output .= "Disallow: /*.{$ext}$\n";
                }

                /**
                 *  Remove line that allows robots to access AJAX interface.
                 *  If no error occurred, replace $output with modified value.
                 */
                // $robots = preg_replace( '/Allow: [^\0\s]*\/wp-admin\/admin-ajax\.php\n/', '', $output );
                // if ( $robots !== null ) {
                //     $output = $robots;
                // }

                // Add sitemap link
                $output .= "\n";
                $output .= "Sitemap: {$site_url[ 'scheme' ]}://{$site_url[ 'host' ]}/sitemap_index.xml\n";
            }

            return $output;
        }

        /**
         * Enqueue styles and scripts in admin
         */
        public function enqueue_admin() {

            // Copy vars shortcut for Notion
            wp_enqueue_script( 'pip-layout-copy-vars', PIP_ADDON_URL . 'assets/js/pip-layout-copy-vars.js', array( 'jquery' ), 1.0, true );

        }

        /**
         *  Use "attachment media url" instead of "attachment page url" by default
         *  when you insert a media in a WYSIWYG
         *
         * @param string $value
         *
         * @return string
         */
        public function attachment_media_url_by_default( $value ) {
            return 'none';
        }

        /**
         *  WordPress - Shortcode - Gallery
         *  - Add "lightbox" on gallery images using "lightbox2"
         *
         * @param $output
         * @param $tag
         * @param $attr
         * @param $regex
         *
         * @return string
         */
        public function gallery_lightbox( $output, $tag, $attr, $regex ) {

            // Only on front-end and using shortcode "gallery"
            if ( $tag !== 'gallery' || is_admin() ) {
                return $output;
            }

            ob_start(); ?>
            <script async="async" src="//cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
            <link href="//cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'"></link>
            <script>
                jQuery( document ).ready( function ( $ ) {
                    var $galleries = $( '.gallery' );
                    if ( !$galleries.length ) {
                        return;
                    }

                    $galleries.each( function ( index ) {
                        var $gallery      = $( this ),
                            $gallery_imgs = $gallery.find( '.gallery-item a' );
                        $gallery_imgs.attr( 'data-lightbox', 'gallery' + index );
                    } );
                } );
            </script>
            <?php
            $output .= ob_get_clean();

            return $output;
        }

        /**
         *  Load WooCommerce templates from Pilo'Press Addon WooCommerce folder
         *
         * @param $template
         * @param $template_name
         * @param $template_path
         *
         * @return string
         */
        public function wc_template_path( $template, $template_name, $template_path ) {

            // 1. If WooCommerce template in theme (default)
            $theme_folder_path = get_stylesheet_directory() . '/woocommerce';
            $theme_template    = trailingslashit( $theme_folder_path ) . $template_name;
            if ( file_exists( $theme_template ) ) {
                return $theme_template;
            }

            // 2. If WooCommerce template in addon
            $addon_folder_path = trailingslashit( PIP_ADDON_PATH ) . 'templates/woocommerce/';
            $addon_template    = trailingslashit( $addon_folder_path ) . $template_name;
            if ( file_exists( $addon_template ) ) {
                return $addon_template;
            }

            // 3. Default template folder
            return $template;
        }

        /**
         *  Add more locations to the main flexible (archives...)
         *
         * @param $locations
         *
         * @return mixed
         */
        public function pip_flexible_locations( $locations ) {

            // Post type archive (ACFE)
            if ( version_compare( ACFE_VERSION, '0.8.7.5', '>=' ) ) {
                $locations[] = array(
                    array(
                        'param'    => 'post_type_archive',
                        'operator' => '==',
                        'value'    => 'all',
                    ),
                );
            }

            // Menu items
            $locations[] = array(
                array(
                    'param'    => 'nav_menu_item',
                    'operator' => '==',
                    'value'    => 'all',
                ),
            );

            // Taxonomies
            $locations[] = array(
                array(
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'all',
                ),
            );

            return $locations;
        }

        /**
         *  Merge "Layouts" location with "Main flexible" location
         *  (so we doesn't have to set manually same location everytime on layouts)
         *
         * @param $field_groups
         *
         * @return mixed
         */
        public function pip_flexible_layouts_locations( $field_groups ) {
            if ( !$field_groups ) {
                return $field_groups;
            }

            foreach ( $field_groups as &$field_group ) {

                // Exclude non-layouts field groups
                if ( acf_maybe_get( $field_group, '_pip_is_layout' ) !== 1 ) {
                    continue;
                }

                // Exclude layout model
                $fg_title = acf_maybe_get( $field_group, 'title' );
                if ( $fg_title === '_layout_model' ) {
                    continue;
                }

                // Add default locations (like pip-pattern...)
                $flexible_locations   = apply_filters( 'pip/builder/locations', array() );
                $flexible_locations[] = array(
                    array(
                        'param'    => 'pip-pattern',
                        'operator' => '==',
                        'value'    => 'all',
                    ),
                );

                $flexible_locations_flat = !empty( $flexible_locations ) ? array_flatten_recursive( $flexible_locations ) : array();

                $layout_locations = acf_maybe_get( $field_group, 'location' );
                foreach ( $layout_locations as $layout_location ) {

                    $layout_param = wp_list_pluck( $layout_location, 'param' );
                    $layout_param = reset( $layout_param );

                    // Add only new location
                    if ( !in_array( $layout_param, $flexible_locations_flat, true ) ) {
                        $flexible_locations[] = $layout_location;
                    }
                }

                $field_group['location'] = $flexible_locations;
            }

            return $field_groups;
        }

        /**
         *  Allow display of native WYSIWYG in product edition
         * (needed for native WooCommerce display)
         *
         * @param $style
         * @param $field_group
         *
         * @return string
         */
        public function pip_display_wysiwyg_on_product( $style, $field_group ) {

            $current_screen   = get_current_screen();
            $screen_base      = pip_maybe_get( $current_screen, 'base' );
            $screen_post_type = pip_maybe_get( $current_screen, 'post_type' );

            if (
                !$current_screen
                || $screen_base !== 'post'
                || $screen_post_type !== 'product'
            ) {
                return $style;
            }

            return '';
        }

        /**
         *  Assets to load on front-end
         */
        public function front_assets() {

            // Variables to pass to front-end JavaScript context
            $pip_js_object = array(
                'ajax'    => admin_url( 'admin-ajax.php' ),
                'theme'   => PIP_THEME_URL,
                'layouts' => PIP_THEME_URL . '/pilopress/layouts',
            );
            wp_localize_script( 'jquery', 'pipAddon', $pip_js_object );

            $front_scripts = array(
                'pip-addon-helpers', // Pilo'Press Addon - Helpers
                'pip-layout-class', // Pilo'Press Addon - Layout class
            );

            foreach ( $front_scripts as $script_name ) {
                $asset_path = "assets/js/$script_name.js";
                if ( !wp_script_is( $script_name ) ) {
                    wp_enqueue_script( $script_name, PIP_ADDON_URL . $asset_path, array( 'jquery' ), filemtime( PIP_ADDON_PATH . $asset_path ), true );
                }
            }

        }

        /**
         *  Dequeue Font Awesome Free CSS
         *
         * @param $load_plugin_fa_css
         *
         * @return false|mixed
         */
        public function dequeue_font_awesome_free( $load_plugin_fa_css ) {

            if ( !is_admin() ) {
                $load_plugin_fa_css = false;
            }

            return $load_plugin_fa_css;
        }

        /**
         *  Enqueue Font Awesome Pro CSS
         */
        public function enqueue_font_awesome_pro() {
            if ( is_admin() ) {
                return;
            }

            // Get latest Font Awesome version from "ACF Font Awesome" plugin
            $fa_version = get_option( 'ACFFA_current_version' );
            if ( !$fa_version ) {
                $fa_version = '5.15.1';
            }

            $fa_url = "https://pro.fontawesome.com/releases/v$fa_version/css/all.css";
            wp_enqueue_style( 'fa-pro', $fa_url, array(), $fa_version );
        }

        /**
         * Add theme supports
         */
        public function init_hook() {

            // Theme support
            add_theme_support( 'custom-logo' );
            add_theme_support( 'post-thumbnails' );
            add_theme_support( 'title-tag' );
            add_theme_support( 'menus' );

            // 3rd party theme support
            add_theme_support( 'woocommerce' );

            // Edit post
            add_post_type_support( 'post', 'excerpt' );
            unregister_taxonomy_for_object_type( 'post_tag', 'post' );

            // Capability
            $capability = apply_filters( 'pip/options/capability', acf_get_setting( 'capability' ) );
            if ( !current_user_can( $capability ) ) {
                return;
            }

            // Add option page
            acf_add_options_page(
                array(
                    'page_title'  => __( 'Settings', 'pip-addon' ),
                    'menu_title'  => __( 'Settings', 'pip-addon' ),
                    'menu_slug'   => 'pip_addon_settings',
                    'capability'  => $capability,
                    'position'    => null,
                    'parent_slug' => 'pilopress',
                    'icon_url'    => '',
                    'redirect'    => true,
                    'post_id'     => 'pip_addon_settings',
                    'autoload'    => false,
                )
            );
        }

        /**
         * Hide Yoast columns
         *
         * @param $result
         * @param $option
         * @param $user
         *
         * @return array
         */
        public function column_hidden( $result, $option, $user ) {
            global $wpdb;

            // Return if user choose which column to display
            if ( $user->has_prop( $wpdb->get_blog_prefix() . $option ) || $user->has_prop( $option ) ) {
                return $result;
            }

            // If not array, set it to array
            if ( !is_array( $result ) ) {
                $result = array();
            }

            // Add Yoast columns
            $result = array_merge(
                $result,
                array(
                    'wpseo-links',
                    'wpseo-score',
                    'wpseo-score-readability',
                    'wpseo-title',
                    'wpseo-metadesc',
                    'wpseo-focuskw',
                )
            );

            // Remove duplicated values
            $result = array_unique( $result );

            return $result;
        }

        /**
         * Add a render field setting to change class output in value
         *
         * @param $field
         */
        public function pip_font_color_settings( $field ) {
            // Get Pilo'Press version
            $pilopress   = acf_get_instance( 'PiloPress' );
            $pip_version = defined( 'PIP_VERSION' ) ? PIP_VERSION : $pilopress::$version;

            if ( version_compare( $pip_version, '0.4.0', '<' ) ) {

                // Select: Class output
                acf_render_field_setting(
                    $field,
                    array(
                        'label'             => __( 'Return Value', 'acf' ),
                        'instructions'      => __( 'Classe retournée dans le champ', 'pip-addon' ),
                        'name'              => 'class_output',
                        'type'              => 'select',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'choices'           => array(
                            'text'       => __( 'Classe de texte', 'pip-addon' ),
                            'background' => __( 'Classe de fond', 'pip-addon' ),
                            'border'     => __( 'Classe de bordure', 'pip-addon' ),
                        ),
                        'default_value'     => 'text',
                        'allow_null'        => 1,
                        'multiple'          => 0,
                        'ui'                => 1,
                        'return_format'     => 'value',
                        'acfe_settings'     => '',
                        'acfe_validate'     => '',
                        'ajax'              => 0,
                        'placeholder'       => '',
                    )
                );
            }

        }

        /**
         * Change class output in format value
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return string|string[]
         */
        public function pip_font_color_format_value( $value, $post_id, $field ) {

            if ( !$value ) {
                return $value;
            }

            $class_output = acf_maybe_get( $field, 'class_output' );
            if ( !$class_output ) {
                return $value;
            }

            // Get Pilo'Press version
            $pilopress   = acf_get_instance( 'PiloPress' );
            $pip_version = defined( 'PIP_VERSION' ) ? PIP_VERSION : $pilopress::$version;

            if ( version_compare( $pip_version, '0.4.0', '<' ) ) {

                switch ( $class_output ) {
                    case 'background':
                        if ( mb_stripos( $value, 'text-' ) === 0 ) {
                            $value = str_replace( 'text-', 'bg-', $value );
                        } else {
                            $value = str_replace( 'bg-', '', $value );
                            $value = 'bg-' . $value;
                        }
                        break;

                    case 'border':
                        if ( mb_stripos( $value, 'text-' ) === 0 ) {
                            $value = str_replace( 'text-', 'border-', $value );
                        } else {
                            $value = str_replace( 'border-', '', $value );
                            $value = 'border-' . $value;
                        }
                        break;

                    case 'text':
                    default:
                        $value = str_replace( 'text-', '', $value );
                        $value = 'text-' . $value;
                        break;
                }
            }

            return $value;
        }

        /**
         * Enqueue GTM script in head
         */
        public function enqueue_gtm() {
            $gtm = get_field( 'gtm', 'pip_addon_settings' );
            if ( $gtm ) :
                ?>
                <script>(
                        function ( w, d, s, l, i ) {
                            w[l] = w[l] || [];
                            w[l].push( { 'gtm.start': new Date().getTime(), event: 'gtm.js' } );
                            var f                            = d.getElementsByTagName( s )[0],
                                j = d.createElement( s ), dl = l != 'dataLayer' ? '&l=' + l : '';
                            j.async                          = true;
                            j.src                            =
                                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                            f.parentNode.insertBefore( j, f );
                        }
                    )( window, document, 'script', 'dataLayer', '<?php echo $gtm; ?>' );
                </script>
                <?php
            endif;
        }

        /**
         * Enqueue GTM no-script after body open tag
         */
        public function enqueue_gtm_noscript() {
            $gtm = get_field( 'gtm', 'pip_addon_settings' );
            if ( $gtm ) :
                ?>
                <noscript>
                    <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm; ?>"
                            height="0" width="0" style="display:none;visibility:hidden"></iframe>
                </noscript>
                <?php
            endif;
        }

        /**
         * Register GMap Api Key for ACF Pro
         *
         * @param $api
         *
         * @return mixed
         */
        public function acf_register_map_api( $api ) {
            $api['key'] = get_field( 'gmap', 'pip_addon_settings' );

            return $api;
        }

        /**
         * Image upload sanitize
         *
         * @param $input
         *
         * @return string
         */
        public function sanitize_file_name( $input ) {
            $path      = pathinfo( $input );
            $extension = ( isset( $path['extension'] ) && !empty( $path['extension'] ) ) ? $path['extension'] : '';
            $file      = ( !empty( $extension ) ) ? preg_replace( '/.' . $extension . '$/', '', $input ) : $input;

            return sanitize_title( str_replace( '_', '-', $file ) ) . ( ( !empty( $extension ) ) ? '.' . $extension : '' );
        }

        /**
         * Icon - Font Awesome
         *
         * @param      $attrs
         * @param null $content
         *
         * @return string
         */
        public function shortcode_icon_fa( $attrs, $content = null ) {

            /**
             * Extract variables from shortcodes attributes
             *
             * @var string $style  Style class (ex: far)
             * @var string $icon   Icon class (ex: fa-paper-plane)
             * @var string $class  Utility classes (ex: fa-fw fa-2x)
             * @var string $link   Link url
             * @var string $target Link target
             * @var string $s      Style class (ex: far) - @deprecated
             * @var string $i      Icon class (ex: fa-paper-plane) - @deprecated
             * @var string $u      Utility classes (ex: fa-fw fa-2x) - @deprecated
             * @var string $l      Link url - @deprecated
             */
            extract( // phpcs:ignore
                shortcode_atts(
                    array(
                        'style'  => '', // Style class (ex: far)
                        'icon'   => '', // Icon class (ex: fa-paper-plane)
                        'class'  => '', // Classes (ex: fa-fw fa-2x text-primary)
                        'link'   => '', // Link url
                        'target' => '', // Link target
                        's'      => '', // Style class (ex: far)
                        'i'      => '', // Icon class (ex: fa-paper-plane)
                        'u'      => '', // Classes (ex: fa-fw fa-2x text-primary)
                        'l'      => '', // Link url
                    ),
                    $attrs
                )
            );

            // Retro-compatibility
            $link  = $l ? $l : $link;
            $style = $s ? $s : $style;
            $icon  = $i ? $i : $icon;
            $class = $u ? $u : $class;

            // Maybe add link
            $html = $link || $l ? '<a href="' . $link . '" target="' . $target . '">' : '';

            // Icon
            $html .= '<i class="pip-shortcode-icon ' . $style . ' ' . $icon . ' ' . $class . '"></i>';

            // Maybe close link
            $html .= $link ? '</a>' : '';

            return $html;
        }

        /**
         * Allow more file types upload
         *
         * @param $mimes
         *
         * @return mixed
         */
        public function upload_mime_types( $mimes ) {
            $mimes['svg']   = 'image/svg+xml';
            $mimes['woff']  = 'application/font-woff';
            $mimes['woff2'] = 'application/font-woff2';

            return $mimes;
        }

        /**
         *  WordPress - Customizer
         *  - Add logo versions in customizer, based on the filter pip_addon/logo_versions
         *
         * @exemple
         * add_filter(
         * 'pip_addon/logo_versions',
         * function( $possible_versions ) {
         *
         *     $possible_versions[] = array(
         *         'label' => __( 'Logo blanc', 'pilot-in' ),
         *         'slug'  => 'logo-white',
         *    );
         *
         *    return $possible_versions;
         *  }
         * );
         */
        public function pip_add_logo_versions_to_customizer( $wp_customize ) {

            $possible_versions = apply_filters( 'pip_addon/logo_versions', array() );
            if ( !$possible_versions ) {
                return;
            }

            foreach ( $possible_versions as $possible_version ) {

                $version_label = pip_maybe_get( $possible_version, 'label' );
                $version_slug  = pip_maybe_get( $possible_version, 'slug' );

                $wp_customize->add_setting(
                    $version_slug,
                    array(
                        'default'    => '',
                        'capability' => 'edit_theme_options',
                    )
                );

                $wp_customize->add_control(
                    new WP_Customize_Image_Control(
                        $wp_customize,
                        $version_slug,
                        array(
                            'label'   => $version_label,
                            'section' => 'title_tagline',
                        )
                    )
                );
            }
        }

        /**
         *  Use templates inside the Pilo'Press Addon
         *
         * @param $template
         *
         * @return string
         */
        public function pip_addon_templates( $template ) {

            // Use "taxonomy.php" template inside the PiloPress-Addon
            if ( is_category() || is_tax() ) {
                // In theme
                if (
                    file_exists( get_stylesheet_directory() . '/taxonomy.php' )
                    || file_exists( get_stylesheet_directory() . '/category.php' )
                ) {
                    return $template;
                }

                // Check if template for specific taxonomy exists in theme
                $taxonomies = get_taxonomies();
                if ( $taxonomies ) {

                    foreach ( $taxonomies as $taxonomy_name ) {
                        // In theme
                        if ( file_exists( get_stylesheet_directory() . '/taxonomy-' . $taxonomy_name . '.php' ) ) {
                            return $template;
                        }
                    }
                }

                // In plugin
                if ( file_exists( PIP_ADDON_PATH . 'templates/taxonomy.php' ) ) {
                    return PIP_ADDON_PATH . 'templates/taxonomy.php';
                }
            }

            // Use "404.php" template inside the PiloPress-Addon
            if ( is_404() ) {
                // In theme
                if ( file_exists( get_stylesheet_directory() . '/404.php' ) ) {
                    return $template;
                }

                // In plugin
                if ( file_exists( PIP_ADDON_PATH . 'templates/404.php' ) ) {
                    return PIP_ADDON_PATH . 'templates/404.php';
                }
            }

            // Use "search.php" template inside the PiloPress-Addon
            if ( is_search() ) {
                // In theme
                if ( file_exists( get_stylesheet_directory() . '/search.php' ) ) {
                    return $template;
                }

                // In plugin
                if ( file_exists( PIP_ADDON_PATH . 'templates/search.php' ) ) {
                    return PIP_ADDON_PATH . 'templates/search.php';
                }
            }

            return $template;
        }

        /**
         *  Update GDPR Add-On String
         */
        public function pip_update_gdpr_content() {

            $is_firstload = get_option( 'pip_addon_gdpr_has_replaced' );
            if ( $is_firstload ) {
                return;
            }

            /**
             *  TODO: Need to check if we can prefill infos / config like we used to do with Cookie Law Info.
             */

            // Buttons
            // update_option(
            //     'CookieLawInfo-0.9',
            //     array(
            //         'button_1_text'          => 'Accepter',
            //         'button_1_button_colour' => '#1e73be',
            //         'button_3_text'          => 'Refuser',
            //         'button_3_button_colour' => '#000',
            //         'button_4_text'          => 'Vos préférences',
            //         'notify_message'         => 'En continuant d\'utiliser le site, vous acceptez l\'utilisation de cookies.[cookie_settings margin="5px 20px 5px 20px"][cookie_button margin="5px"]',
            //     )
            // );

            // Non necessary cookies
            // update_option(
            //     'cookielawinfo_thirdparty_settings',
            //     array(
            //         'thirdparty_title'       => 'Cookies non nécessaires',
            //         'thirdparty_description' => 'Tous les cookies qui peuvent ne pas être particulièrement nécessaires au fonctionnement du site Web et qui sont utilisés spécifiquement pour collecter des données personnelles des utilisateurs via des analyses, des publicités ou tout autre contenu intégré sont qualifiés de cookies non nécessaires. Il est obligatoire d\'obtenir le consentement de l\'utilisateur avant d\'exécuter ces cookies sur votre site Web.',
            //     )
            // );

            // Necessary cookies
            // update_option(
            //     'cookielawinfo_necessary_settings',
            //     array(
            //         'necessary_title'       => 'Cookies nécessaires',
            //         'necessary_description' => 'Les cookies nécessaires sont absolument essentiels au bon fonctionnement du site. Cette catégorie inclut uniquement les cookies qui garantissent les fonctionnalités de base et les fonctionnalités de sécurité du site Web. Ces cookies ne stockent aucune information personnelle.',
            //     )
            // );

            // Politique de confidentialité
            // $privacy_page_id  = get_option( 'wp_page_for_privacy_policy' );
            // $privacy_page_url = get_the_permalink( $privacy_page_id );
            // update_option(
            //     'cookielawinfo_privacy_overview_content_settings',
            //     array(
            //         'privacy_overview_title'   => 'Apercu de confidentialité',
            //         'privacy_overview_content' => '<a href="' . $privacy_page_url . '">Politique de confidentialité</a>',
            //     )
            // );

            add_option( 'pip_addon_gdpr_has_replaced', true, '', true );
        }

        /**
         *  Edit PIP Flexible args
         *
         * @param $params
         *
         * @return mixed
         */
        public function pip_flexible_args( $params ) {

            $params['acfe_flexible_advanced']                         = 1;           // Toggle advanced flexible features mode
            $params['acfe_flexible_toggle']                           = 1;           // Toggle layout visibilty on front-end
            $params['acfe_flexible_layouts_state']                    = 'collapsed'; // Force layouts to be on "closed / preview"
            $params['acfe_flexible_modal_edition']                    = 1;           // Show layout edition inside a modal
            $params['acfe_flexible_modal']['acfe_flexible_modal_col'] = 4;           // Set 4 layouts per row in layouts selection

            return $params;
        }

    }

    // Instantiate
    new PIP_Addon_Main();
}

<?php

if ( !class_exists( 'PIP_CleanUp' ) ) {

    /**
     * Clean up wp_head()
     * (fork of roots/soil @https://github.com/roots/soil/blob/main/src/Modules/CleanUpModule.php)
     *
     * Remove unnecessary <link>'s
     * Remove inline CSS and JS from WP emoji support
     * Remove inline CSS used by Recent Comments widget
     * Remove inline CSS used by posts with galleries
     * Remove self-closing tag
     */
    class PIP_CleanUp {

        /**
         * Name of the module.
         *
         * @var string
         */
        protected $name = 'pip-clean-up';

        /**
         * Module handle.
         *
         * @return void
         */
        public function __construct() {
            $tasks = array(
                'wp_obscurity'                => 'wp_obscurity',
                'disable_emojis'              => 'disable_emojis',
                'disable_gutenberg_block_css' => 'disable_gutenberg_block_css',
                'disable_extra_rss'           => 'disable_extra_rss',
                'disable_recent_comments_css' => 'disable_recent_comments_css',
                'disable_gallery_css'         => 'disable_gallery_css',
                'clean_html5_markup'          => 'clean_html_markup',
            );

            foreach ( $tasks as $task ) {
                if ( isset( $tasks[ $task ] ) ) {
                    $this->{$tasks[ $task ]}();
                }
            }
        }

        /**
         * Obscure and suppress WordPress information.
         *
         * @return void
         */
        protected function wp_obscurity() {
            add_filter( 'get_bloginfo_rss', array( $this, 'remove_default_site_tagline' ) );
            add_filter( 'the_generator', '__return_false' );
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
            remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
            remove_action( 'wp_head', 'wp_generator' );
            remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
            remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
            remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        }

        /**
         * Disable WordPress emojis.
         *
         * @return void
         */
        protected function disable_emojis() {
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
            add_filter( 'emoji_svg_url', '__return_false' );
        }

        /**
         * Disable Gutenberg block library CSS.
         *
         * @return void
         */
        protected function disable_gutenberg_block_css() {
            add_action(
                'wp_enqueue_scripts',
                function () {
                    wp_dequeue_style( 'wp-block-library' );
                },
                200
            );
        }

        /**
         * Disable extra RSS feeds.
         *
         * @return void
         */
        protected function disable_extra_rss() {
            add_filter( 'feed_links_show_comments_feed', '__return_false' );
        }

        /**
         * Disable recent comments CSS.
         *
         * @return void
         */
        protected function disable_recent_comments_css() {
            add_filter( 'show_recent_comments_widget_style', '__return_false' );
        }

        /**
         * Disable gallery CSS.
         *
         * @return void
         */
        protected function disable_gallery_css() {
            add_filter( 'use_default_gallery_style', '__return_false' );
        }

        /**
         * Clean HTML5 markup.
         *
         * @return void
         */
        protected function clean_html_markup() {
            add_filter( 'body_class', 'body_class' );
            add_filter( 'language_attributes', 'language_attributes' );
            add_filter( 'get_avatar', 'remove_self_closing_tags' );
            add_filter( 'comment_id_fields', 'remove_self_closing_tags' );
            add_filter( 'post_thumbnail_html', 'remove_self_closing_tags' );

            add_filter(
                'site_icon_meta_tags',
                function ( $meta_tags ) {
                    return array_map( array( $this, 'remove_self_closing_tags' ), $meta_tags );
                },
                20
            );
        }

        /**
         * Clean up language_attributes() used in <html> tag
         *
         * Remove dir="ltr"
         *
         * @return void
         * @internal Used by `language_attributes`
         *
         */
        public function language_attributes() {
            $attributes = array();

            if ( is_rtl() ) {
                $attributes[] = 'dir="rtl"';
            }

            $lang = get_bloginfo( 'language' );

            if ( $lang ) {
                $attributes[] = "lang=\"{$lang}\"";
            }

            return implode( ' ', $attributes );
        }

        /**
         * Add and remove body_class() classes.
         *
         * @param array $classes
         *
         * @return array
         * @internal Used by `body_class`
         *
         */
        public function body_class( $classes ) {
            $remove_classes = array(
                'page-template-default',
            );

            // Add post/page slug if not present
            if ( is_single() || is_page() && !is_front_page() ) {
                $slug = basename( get_permalink() );
                if ( !in_array( $slug, $classes, true ) ) {
                    $classes[] = $slug;
                }
            }

            if ( is_front_page() ) {
                $remove_classes[] = 'page-id-' . get_option( 'page_on_front' );
            }

            $classes = array_values( array_diff( $classes, $remove_classes ) );

            return $classes;
        }

        /**
         * Remove the default site tagline from RSS feed.
         *
         * @param string $bloginfo
         *
         * @return string
         * @internal Used by `get_bloginfo_rss`
         *
         */
        public function remove_default_site_tagline( $bloginfo ) {
            $default_tagline = __( 'Just another WordPress site' );

            return ( $bloginfo === $default_tagline ) ? '' : $bloginfo;
        }

        /**
         * Remove self-closing tags.
         *
         * @param string|string[] $html
         *
         * @return string|string[]
         * @internal Used by `get_avatar`, `comment_id_fields`, and `post_thumbnail_html`
         *
         */
        public function remove_self_closing_tags( $html ) {
            return str_replace( ' />', '>', $html );
        }
    }
}

new PIP_CleanUp();

// STATS
// Without PIP CleanUp: 86 requests / 5.2MB resources / Finish 7.11s / DomLoaded 3.42s
// With PIP CleanUp: 84 requests / 5.2MB resources / Finish 6.71s / DomLoaded 2.57s

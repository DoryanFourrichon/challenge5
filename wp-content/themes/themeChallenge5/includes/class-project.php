<?php

if ( !class_exists( 'Project' ) ) {

    /**
     * Class Project
     */
    class Project {

        /**
         * Project constructor.
         */
        public function __construct() {

            // Check for Pilo'Press first
            if ( !class_exists( 'PiloPress' ) ) {
                return;
            }

            // WP hooks
            add_action( 'init', array( $this, 'init_hook' ) );

            // Pilo'Press hooks
            add_filter( 'pip/tailwind/css/after_components', array( $this, 'add_custom_css' ) );

            // Local compilation
            /*add_filter( 'pip/tailwind_api', '__return_false' );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
            add_filter( 'mce_css', array( $this, 'editor_style' ), 20 );*/
        }

        /**
         * Init hook.
         */
        public function init_hook() {
            // Rename "Post" post type
            $get_post_type              = get_post_type_object( 'post' );
            $labels                     = pip_maybe_get( $get_post_type, 'labels' );
            $labels->name               = __( 'Actualités', 'pilot-in' );
            $labels->singular_name      = __( 'Actualités', 'pilot-in' );
            $labels->add_new            = __( 'Ajouter une actualité', 'pilot-in' );
            $labels->add_new_item       = __( 'Ajouter une actualité', 'pilot-in' );
            $labels->edit_item          = __( 'Éditer une actualité', 'pilot-in' );
            $labels->new_item           = __( 'Nouvelle actualité', 'pilot-in' );
            $labels->view_item          = __( 'Voir les actualités', 'pilot-in' );
            $labels->search_items       = __( 'Chercher une ctualités', 'pilot-in' );
            $labels->not_found          = __( 'Aucune actualité trouvée', 'pilot-in' );
            $labels->not_found_in_trash = __( 'Aucune actualité trouvée dans la corbeille', 'pilot-in' );
            $labels->all_items          = __( 'Toutes les actualités', 'pilot-in' );
            $labels->menu_name          = __( 'Actualités', 'pilot-in' );
            $labels->name_admin_bar     = __( 'Actualités', 'pilot-in' );

            // Remove "post_tag" taxonomy
            register_taxonomy( 'post_tag', array() );

            // Remove "product_tag" taxonomy
            register_taxonomy( 'product_tag', array() );

            // Rename "Category" taxonomy
            global $wp_taxonomies;
            $tax_labels                        = array(
                'name'          => __( "Catégorie d'actualités", 'pilot-in' ),
                'singular_name' => __( "Catégorie d'actualités", 'pilot-in' ),
                'menu_name'     => __( "Catégories d'actualités", 'pilot-in' ),
                'all_items'     => __( "Toutes les catégories d'actualités", 'pilot-in' ),
                'edit_item'     => __( "Éditer la catégorie d'actualités", 'pilot-in' ),
                'view_item'     => __( "Voir la catégorie d'actualités", 'pilot-in' ),
                'update_item'   => __( "Mettre à jour la catégorie d'actualités", 'pilot-in' ),
                'add_new_item'  => __( "Ajouter une catégorie d'actualités", 'pilot-in' ),
                'new_item_name' => __( "Nom de la nouvelle catégorie d'actualités", 'pilot-in' ),
                'parent_item'   => __( "Catégorie d'actualités parente", 'pilot-in' ),
                'search_items'  => __( "Chercher une catégorie d'actualités", 'pilot-in' ),
                'popular_items' => __( 'Éléments populaires', 'pilot-in' ),
            );
            $wp_taxonomies['category']->labels = (object) array_merge( (array) $wp_taxonomies['category']->labels, $tax_labels );
        }

        /**
         * Enqueue styles and scripts
         */
        public function enqueue_front() {
            // Your code here
        }

        /**
         * Enqueue styles and scripts
         */
        public function enqueue_admin() {
            // Enqueue Tailwind Styles
            $admin_style_path = PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css';
            if ( file_exists( $admin_style_path ) ) {
                wp_enqueue_style(
                    'tailwind-styles-admin',
                    PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_ADMIN_FILENAME . '.min.css',
                    null,
                    filemtime( $admin_style_path )
                );
            }
        }

        /**
         * Add custom editor style
         *
         * @param $stylesheets
         *
         * @return string
         */
        public function editor_style( $stylesheets ) {

            // Get stylesheets
            $stylesheets = explode( ',', $stylesheets );

            // Add custom stylesheet
            if ( file_exists( PIP_THEME_ASSETS_PATH . PIP_THEME_STYLE_FILENAME . '.min.css' ) ) {
                $stylesheets[] = PIP_THEME_ASSETS_URL . PIP_THEME_STYLE_FILENAME . '.min.css';
            }

            return implode( ',', $stylesheets );
        }

        /**
         * Add custom CSS for Tailwind compilation
         *
         * @return string
         */
        public function add_custom_css() {
            return file_get_contents( get_stylesheet_directory() . '/style.css' );
        }

    }

    /**
     * Instantiate class
     * Use "acf_get_instance( 'Project' )" to get class and use functions inside it
     *
     * @see acf_new_instance()
     * @see acf_get_instance()
     */
    if ( function_exists( 'acf_new_instance' ) ) {
        acf_new_instance( 'Project' );
    }
}

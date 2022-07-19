<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Addon_Menus' ) ) {

    /**
     * Class PIP_Addon_Menus
     */
    class PIP_Addon_Menus {

        /**
         * PIP_Addon_Menus constructor.
         */
        public function __construct() {
            add_action( 'admin_menu', array( $this, 'admin_menu_hook' ) );
            add_action( 'wp_before_admin_bar_render', array( $this, 'remove_useless_bar_menus' ) );
            add_filter( 'nav_menu_css_class', array( $this, 'menu_item_parent_css_class' ), 10, 4 );
            add_filter( 'nav_menu_submenu_css_class', array( $this, 'menu_item_submenu_css_class' ), 10, 4 );
            add_filter( 'wp_nav_menu_objects', array( $this, 'menu_items_fa_icons' ), 9, 2 );
            add_action( 'admin_footer', array( $this, 'nav_menu_items_display' ) );
        }

        /**
         * Admin menu hook.
         */
        public function admin_menu_hook() {
            /**
             * Remove some menus
             */
            remove_menu_page( 'edit-comments.php' );

            /**
             * Move comments into post_type post submenu
             */
            add_submenu_page(
                'edit.php',
                __( 'Comments', 'acf' ),
                __( 'Comments', 'acf' ),
                'manage_options',
                'edit-comments.php'
            );
            remove_menu_page( 'edit-comments.php' );

            /**
             * Move post_type "Page" to top + Add separator after "post_types"
             */
            global $menu;

            // Page
            $menu['4.5'] = $menu[20];
            unset( $menu[20] );

            // Separator
            $menu['8.5'] = array(
                '',
                'read',
                'separator8.5',
                '',
                'wp-menu-separator',
            );
        }

        /**
         * Remove some menus
         */
        public function remove_useless_bar_menus() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu( 'comments' );
        }

        /**
         *  Menu - Submenu
         *  - Add "tailwind" classes on parent menu items
         *
         * @param $classes
         * @param $item
         * @param $args
         * @param $depth
         *
         * @return array
         */
        public function menu_item_parent_css_class( $classes, $item, $args, $depth ) {

            // Only first-level
            if ( $depth !== 0 ) {
                return $classes;
            }

            // Skip non-parent menu items
            if ( !array_search( 'menu-item-has-children', $classes, true ) ) {
                return $classes;
            }

            $new_classes = 'group';
            $new_classes = apply_filters( 'pip_addon/parent_menu_item/classes', $new_classes, $item, $args, $depth );
            $new_classes = explode( ' ', $new_classes );
            $classes     = array_merge( $classes, $new_classes );

            return $classes;
        }

        /**
         *  Menu - Submenu
         *  - Add "tailwind" classes on parent menu items
         *
         * @param $classes
         * @param $args
         * @param $depth
         *
         * @return array
         */
        public function menu_item_submenu_css_class( $classes, $args, $depth ) {
            // Only first-level
            if ( $depth !== 0 ) {
                return $classes;
            }

            $new_classes  = 'flex items-center justify-center h-full';
            $new_classes .= ' lg:h-auto lg:absolute lg:hidden lg:group-hover:block lg:top-full lg:right-0 lg:p-3 lg:shadow lg:bg-white lg:min-w-max';
            $new_classes = apply_filters( 'pip_addon/submenu_item/classes', $new_classes, $args, $depth );
            $new_classes = explode( ' ', $new_classes );
            $classes     = array_merge( $classes, $new_classes );

            return $classes;
        }

        /**
         *  Menu item - Add "Font Awesome" icon to "text"
         *
         * @param $items
         * @param $args
         *
         * @return mixed
         */
        public function menu_items_fa_icons( $items, $args ) {

            // If no menu items, return
            if ( !$items ) {
                return $items;
            }

            // Is RTL language
            $rtl = pip_is_rtl();

            // Browse menu items
            foreach ( $items as &$item ) {

                // If no icon, skip
                $show_menu_icon = get_field( 'menu_icon_switch', $item );
                if ( !$show_menu_icon ) {
                    continue;
                }

                // Get icon params
                $menu_icon           = get_field( 'menu_icon', $item );
                $menu_icon_position  = get_field( 'menu_icon_placement', $item );
                $menu_icon_hide_text = get_field( 'menu_icon_hide_text', $item );
                $menu_icon_color     = get_field( 'menu_icon_color', $item );
                $old_item_title      = pip_maybe_get( $item, 'title' );

                // Hide text
                if ( $menu_icon_hide_text ) {

                    $menu_icon_class = "fa-fw $menu_icon_color";
                    $menu_icon_class = apply_filters( 'pip_addon/menu_icon/class', $menu_icon_class );
                    $menu_icon       = str_replace( 'class="', 'class="' . $menu_icon_class . ' ', $menu_icon );
                    $item->title     = $menu_icon;

                } else {

                    // Menu icon position
                    if ( $menu_icon_position === 'gauche' || $menu_icon_position === 'left' ) {

                        $margin          = $rtl ? 'ml-2' : 'mr-2';
                        $margin          = apply_filters( 'pip_addon/menu_icon/margin', $margin, $rtl, $args, $menu_icon_position );
                        $menu_icon_class = "fa-fw $menu_icon_color $margin";
                        $menu_icon_class = apply_filters( 'pip_addon/menu_icon/class', $menu_icon_class );
                        $menu_icon       = str_replace( 'class="', 'class="' . $menu_icon_class . ' ', $menu_icon );
                        $item->title     = $menu_icon . $old_item_title;

                    } elseif ( $menu_icon_position === 'droite' || $menu_icon_position === 'right' ) {

                        $margin          = $rtl ? 'mr-2' : 'ml-2';
                        $margin          = apply_filters( 'pip_addon/menu_icon/margin', $margin, $rtl, $args, $menu_icon_position );
                        $menu_icon_class = "fa-fw $menu_icon_color $margin";
                        $menu_icon_class = apply_filters( 'pip_addon/menu_icon/class', $menu_icon_class );
                        $menu_icon       = str_replace( 'class="', 'class="' . $menu_icon_class . ' ', $menu_icon );
                        $item->title     = $old_item_title . $menu_icon;

                    }
                }
            }

            return $items;
        }

        /**
         *  Admin footer - CSS
         *  - Display "menu items" full-width in the admin
         */
        public function nav_menu_items_display() {

            // validate screen
            if ( !acf_is_screen( 'nav-menus' ) ) {
                return;
            } ?>
            <style>
                .menu-item-bar .menu-item-handle {
                    box-sizing: border-box;
                    max-width: none;
                }

                .menu-item .menu-item-settings {
                    max-width: none;
                }
            </style>
            <?php
        }

    }

    // Instantiate
    new PIP_Addon_Menus();
}

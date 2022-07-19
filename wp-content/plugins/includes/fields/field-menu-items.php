<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Addon_Field_Menu_Items' ) ) {

    /**
     * Class PIP_Addon_Field_Menu_Items
     */
    class PIP_Addon_Field_Menu_Items extends acf_field {

        /**
         * PIP_Addon_Field_Menu_Items constructor.
         */
        public function __construct() {

            $this->name     = 'pip_addon_field_menu_items';
            $this->label    = __( 'Menu items', 'pip-addon' );
            $this->category = 'relational';
            $this->defaults = array(
                'menu_items'         => array(),
                'field_type'         => 'checkbox',
                'multiple'           => 0,
                'allow_null'         => 0,
                'choices'            => array(),
                'default_value'      => '',
                'ui'                 => 0,
                'ajax'               => 0,
                'placeholder'        => '',
                'search_placeholder' => '',
                'layout'             => '',
                'toggle'             => 0,
                'allow_custom'       => 0,
                'return_format'      => 'name',
            );

            parent::__construct();

        }

        /**
         * Render field settings
         *
         * @param $field
         */
        public function render_field_settings( $field ) {

            if ( isset( $field['default_value'] ) ) {
                $field['default_value'] = acf_encode_choices( $field['default_value'], false );
            }

            $menu_items_data = get_posts(
                array(
                    'post_type'      => 'nav_menu_item',
                    'post_status'    => 'publish',
                    'order'          => 'ASC',
                    'orderby'        => 'menu_order',
                    'output'         => ARRAY_A,
                    'output_key'     => 'menu_order',
                    'nopaging'       => true,
                    'posts_per_page' => '-1',
                )
            );

            $menu_items_ids    = !empty( $menu_items_data ) ? wp_list_pluck( $menu_items_data, 'ID' ) : array();
            $menu_items_titles = !empty( $menu_items_data ) ? wp_list_pluck( $menu_items_data, 'post_title' ) : array();
            $menu_items        = array_combine( $menu_items_ids, $menu_items_titles );

            // Allow Menu Items
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Allow Menu Items', 'acf' ),
                    'instructions' => '',
                    'type'         => 'select',
                    'name'         => 'menu_items',
                    'choices'      => $menu_items,
                    'multiple'     => 1,
                    'ui'           => 1,
                    'allow_null'   => 1,
                    'placeholder'  => __( 'All menu items', 'acf' ),
                )
            );

            // field_type
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Appearance', 'acf' ),
                    'instructions' => __( 'Select the appearance of this field', 'acf' ),
                    'type'         => 'select',
                    'name'         => 'field_type',
                    'optgroup'     => true,
                    'choices'      => array(
                        'checkbox' => __( 'Checkbox', 'acf' ),
                        'radio'    => __( 'Radio Buttons', 'acf' ),
                        'select'   => _x( 'Select', 'noun', 'acf' ),
                    ),
                )
            );

            // default_value
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Default Value', 'acf' ),
                    'instructions' => __( 'Enter each default value on a new line', 'acf' ),
                    'name'         => 'default_value',
                    'type'         => 'textarea',
                )
            );

            // return_format
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Return Value', 'acf' ),
                    'instructions' => '',
                    'type'         => 'radio',
                    'name'         => 'return_format',
                    'choices'      => array(
                        'object' => __( 'Menu object', 'acfe' ),
                        'name'   => __( 'Menu name', 'acfe' ),
                    ),
                    'layout'       => 'horizontal',
                )
            );

            // Select + Radio: allow_null
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Allow Null?', 'acf' ),
                    'instructions' => '',
                    'name'         => 'allow_null',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                        ),
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'radio',
                            ),
                        ),
                    ),
                )
            );

            // Select: multiple
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Select multiple values?', 'acf' ),
                    'instructions' => '',
                    'name'         => 'multiple',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                        ),
                    ),
                )
            );

            // Select: ui
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Stylised UI', 'acf' ),
                    'instructions' => '',
                    'name'         => 'ui',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                        ),
                    ),
                )
            );

            // Select: ajax
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Use AJAX to lazy load choices?', 'acf' ),
                    'instructions' => '',
                    'name'         => 'ajax',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                            array(
                                'field'    => 'ui',
                                'operator' => '==',
                                'value'    => 1,
                            ),
                        ),
                    ),
                )
            );

            // placeholder
            acf_render_field_setting(
                $field,
                array(
                    'label'             => __( 'Placeholder', 'acf' ),
                    'instructions'      => __( 'Appears within the input', 'acf' ),
                    'type'              => 'text',
                    'name'              => 'placeholder',
                    'placeholder'       => _x( 'Select', 'verb', 'acf' ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                            array(
                                'field'    => 'allow_null',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                            array(
                                'field'    => 'ui',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                            array(
                                'field'    => 'allow_null',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                    ),
                )
            );

            // search placeholder
            acf_render_field_setting(
                $field,
                array(
                    'label'             => __( 'Search Input Placeholder', 'acf' ),
                    'instructions'      => __( 'Appears within the search input', 'acf' ),
                    'type'              => 'text',
                    'name'              => 'search_placeholder',
                    'placeholder'       => _x( 'Select', 'verb', 'acf' ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                            array(
                                'field'    => 'ui',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                    ),
                )
            );

            // Radio: other_choice
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Other', 'acf' ),
                    'instructions' => '',
                    'name'         => 'other_choice',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'message'      => __( "Add 'other' choice to allow for custom values", 'acf' ),
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'radio',
                            ),
                        ),
                    ),
                )
            );

            // Checkbox: layout
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Layout', 'acf' ),
                    'instructions' => '',
                    'type'         => 'radio',
                    'name'         => 'layout',
                    'layout'       => 'horizontal',
                    'choices'      => array(
                        'vertical'   => __( 'Vertical', 'acf' ),
                        'horizontal' => __( 'Horizontal', 'acf' ),
                    ),
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'checkbox',
                            ),
                        ),
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'radio',
                            ),
                        ),
                    ),
                )
            );

            // Checkbox: toggle
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Toggle', 'acf' ),
                    'instructions' => __( 'Prepend an extra checkbox to toggle all choices', 'acf' ),
                    'name'         => 'toggle',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'checkbox',
                            ),
                        ),
                    ),
                )
            );

            // Checkbox: other_choice
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Allow Custom', 'acf' ),
                    'instructions' => '',
                    'name'         => 'allow_custom',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'message'      => __( "Allow 'custom' values to be added", 'acf' ),
                    'conditions'   => array(
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'checkbox',
                            ),
                        ),
                        array(
                            array(
                                'field'    => 'field_type',
                                'operator' => '==',
                                'value'    => 'select',
                            ),
                            array(
                                'field'    => 'ui',
                                'operator' => '==',
                                'value'    => '1',
                            ),
                        ),
                    ),
                )
            );

        }

        /**
         * Prepare field
         *
         * @param $field
         *
         * @return mixed
         */
        public function prepare_field( $field ) {

            // Set Field Type
            $field['type'] = $field['field_type'];

            // Choices
            $menu_items_data = get_posts(
                array(
                    'post_type'      => 'nav_menu_item',
                    'post_status'    => 'publish',
                    'order'          => 'ASC',
                    'orderby'        => 'menu_order',
                    'output'         => ARRAY_A,
                    'output_key'     => 'menu_order',
                    'nopaging'       => true,
                    'posts_per_page' => '-1',
                )
            );

            if ( empty( $menu_items_data ) ) {
                return $field;
            }

            $menu_items_ids    = wp_list_pluck( $menu_items_data, 'ID' );
            $menu_items_titles = wp_list_pluck( $menu_items_data, 'post_title' );
            $menu_items        = array_combine( $menu_items_ids, $menu_items_titles );

            if ( !$menu_items ) {
                return $field;
            }

            $allowed_menu_items = acf_maybe_get( $field, 'menu_items' );
            if ( $allowed_menu_items ) {
                foreach ( $menu_items as $menu_item_id => $menu_item_label ) {
                    if ( !in_array( $menu_item_id, $allowed_menu_items ) ) {
                        unset( $menu_items[ $menu_item_id ] );
                    }
                }
            }

            $field['choices'] = $menu_items;

            // Allow Custom
            if ( acf_maybe_get( $field, 'allow_custom' ) ) {

                if ( $value = acf_maybe_get( $field, 'value' ) ) {

                    $value = acf_get_array( $value );

                    foreach ( $value as $v ) {

                        if ( isset( $field['choices'][ $v ] ) ) {
                            continue;
                        }

                        $field['choices'][ $v ] = $v;

                    }
                }
            }

            return $field;

        }

        /**
         * Format value
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return int[]|mixed|WP_Post[]
         */
        public function format_value( $value, $post_id, $field ) {

            // Return: object
            if ( $field['return_format'] === 'object' ) {

                // array
                if ( acf_is_array( $value ) ) {

                    foreach ( $value as $i => $v ) {

                        $menu_item_data = get_posts(
                            array(
                                'post_type'      => 'nav_menu_item',
                                'post_status'    => 'publish',
                                'post__in'       => array( $i ),
                                'order'          => 'ASC',
                                'orderby'        => 'menu_order',
                                'output'         => ARRAY_A,
                                'output_key'     => 'menu_order',
                                'nopaging'       => true,
                                'posts_per_page' => '-1',
                            )
                        );

                        if ( $menu_item_data ) {

                            $value[ $i ] = $menu_item_data;

                        } else {

                            $value[ $i ] = $i;

                        }
                    }

                    // string
                } else {

                    $menu_item_data = get_posts(
                        array(
                            'post_type'      => 'nav_menu_item',
                            'post_status'    => 'publish',
                            'post__in'       => array( $value ),
                            'order'          => 'ASC',
                            'orderby'        => 'menu_order',
                            'output'         => ARRAY_A,
                            'output_key'     => 'menu_order',
                            'nopaging'       => true,
                            'posts_per_page' => '-1',
                        )
                    );

                    if ( $menu_item_data ) {
                        $value = $menu_item_data;
                    }
                }
            }

            // return
            return $value;

        }

    }

    // Initialize
    acf_register_field_type( 'pip_addon_field_menu_items' );

}

<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Addon_Field_Menus' ) ) {

    /**
     * Class PIP_Addon_Field_Menus
     */
    class PIP_Addon_Field_Menus extends acf_field {

        /**
         * PIP_Addon_Field_Menus constructor.
         */
        public function __construct() {

            $this->name     = 'pip_addon_field_menus';
            $this->label    = __( 'Menus', 'pip-addon' );
            $this->category = 'relational';
            $this->defaults = array(
                'menu'               => array(),
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
         * Render field
         *
         * @param $field
         */
        public function render_field_settings( $field ) {

            if ( isset( $field['default_value'] ) ) {
                $field['default_value'] = acf_encode_choices( $field['default_value'], false );
            }

            $menus_data  = wp_get_nav_menus();
            $menu_labels = !empty( $menus_data ) ? wp_list_pluck( $menus_data, 'name' ) : array();
            $menu_slugs  = !empty( $menus_data ) ? wp_list_pluck( $menus_data, 'slug' ) : array();
            $menus       = array_combine( $menu_slugs, $menu_labels );

            // Allow Menus
            acf_render_field_setting(
                $field,
                array(
                    'label'        => __( 'Allow Menus', 'acf' ),
                    'instructions' => '',
                    'type'         => 'select',
                    'name'         => 'menu',
                    'choices'      => $menus,
                    'multiple'     => 1,
                    'ui'           => 1,
                    'allow_null'   => 1,
                    'placeholder'  => __( 'All menus', 'acf' ),
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
            $menus_data = wp_get_nav_menus();
            if ( !$menus_data || empty( $menus_data ) ) {
                return $field;
            }

            $menu_ids    = wp_list_pluck( $menus_data, 'slug' );
            $menu_labels = wp_list_pluck( $menus_data, 'name' );
            $menus       = array_combine( $menu_ids, $menu_labels );

            if ( !$menus ) {
                return $field;
            }

            $allowed_menus = acf_maybe_get( $field, 'menu' );
            if ( $allowed_menus ) {
                foreach ( $menus as $menu_slug => $menu_label ) {
                    if ( !in_array( $menu_slug, $allowed_menus ) ) {
                        unset( $menus[ $menu_slug ] );
                    }
                }
            }

            $field['choices'] = $menus;

            // Allow Custom
            if ( acf_maybe_get( $field, 'allow_custom' ) ) {

                $value = acf_maybe_get( $field, 'value' );
                if ( $value ) {

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
         * @return mixed|WP_Term
         */
        public function format_value( $value, $post_id, $field ) {

            // Return: object
            if ( $field['return_format'] === 'object' ) {

                // array
                if ( acf_is_array( $value ) ) {

                    foreach ( $value as $i => $v ) {

                        $get_menu = wp_get_nav_menu_object( $v );
                        if ( $get_menu ) {

                            $value[ $i ] = $get_menu;

                        } else {

                            $value[ $i ] = $i;

                        }
                    }

                    // string
                } else {

                    $get_menu = wp_get_nav_menu_object( $value );
                    if ( $get_menu ) {
                        $value = $get_menu;
                    }
                }
            }

            // return
            return $value;

        }

    }

    // Initialize
    acf_register_field_type( 'pip_addon_field_menus' );

}

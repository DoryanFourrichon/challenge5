<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'PIP_Font_Color_Field' ) ) {

    /**
     * Class PIP_Font_Color_Field
     */
    class PIP_Font_Color_Field extends acf_field {

        public function __construct() {

            $this->name     = 'pip_font_color';
            $this->label    = __( 'Theme colors', 'pilopress' );
            $this->category = __( "Pilo'Press", 'pilopress' );
            $this->defaults = array(
                'field_type'        => 'select',
                'choices'           => array(),
                'placeholder'       => '',
                'return_format'     => 'value',
                'pip_default_value' => '',
                'allow_null'        => true,
                'other_choice'      => 0,
                'allow_custom'      => 0,
                'ajax'              => false,
            );

            parent::__construct();
        }

        /**
         * Get choices
         *
         * @param $show_add_to_editor
         *
         * @return array
         */
        public function get_choices( $show_add_to_editor ) {

            // Enable ACF "Local" mode if not active yet to get data from local fields
            $acf_local_was_active = acf_is_local_enabled();
            if ( !$acf_local_was_active ) {
                acf_enable_local();
            }

            // Get class instance
            $pip_tinymce = acf_get_instance( 'PIP_TinyMCE' );

            // Add custom choices
            $choices       = array();
            $custom_styles = $pip_tinymce->get_custom_colors();
            if ( $custom_styles ) {
                foreach ( $custom_styles as $key => $custom_style ) {

                    // If only show editor colors checked, skip if color not in editor
                    if ( $show_add_to_editor && !$custom_style['add_to_editor'] ) {
                        continue;
                    }

                    $choices[ $key ] = $custom_style['name'];
                }
            }

            // Restore ACF "Local" mode initial state after we get our data.
            if ( !$acf_local_was_active ) {
                acf_disable_local();
            }

            return $choices;
        }

        /**
         * Prepare field
         *
         * @param $field
         *
         * @return mixed
         */
        public function prepare_field( $field ) {

            // Only show items with "Add to editor" option
            $show_add_to_editor = acf_maybe_get( $field, 'show_add_to_editor' );

            // Default value
            $field_default_value = acf_maybe_get( $field, 'pip_default_value' );
            $field_value         = acf_maybe_get( $field, 'value' );
            if ( !$field_value && $field_default_value ) {
                $field['value'] = $field_default_value;
            }

            $field['choices'] = $this->get_choices( $show_add_to_editor );
            $field['type']    = $field['field_type'];

            return $field;
        }

        /**
         * Render field
         *
         * @param $field
         */
        public function render_field( $field ) {

            $value   = acf_get_array( $field['value'] );
            $choices = acf_get_array( $field['choices'] );

            // Placeholder
            if ( empty( $field['placeholder'] ) ) {
                $field['placeholder'] = _x( 'Select', 'verb', 'acf' );
            }

            // Add empty value (allows '' to be selected)
            if ( empty( $value ) ) {
                $value = array( '' );
            }

            $select = array(
                'id'    => $field['id'],
                'class' => $field['class'],
                'name'  => $field['name'],
            );

            $select['value']   = $value;
            $select['choices'] = $choices;

            acf_select_input( $select );
        }

        /**
         * Render field settings
         *
         * @param $field
         */
        public function render_field_settings( $field ) {

            // Field type
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

            // Placeholder
            acf_render_field_setting(
                $field,
                array(
                    'label'             => __( 'Placeholder Text', 'acf' ),
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

            // Select: UI
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

            // Select: Default value
            acf_render_field_setting(
                $field,
                array(
                    'label'         => __( 'Default Value', 'acf' ),
                    'type'          => 'select',
                    'name'          => 'pip_default_value',
                    'required'      => 0,
                    'allow_null'    => 1,
                    'return_format' => 'value',
                    'choices'       => $this->get_choices( 0 ),
                )
            );

            // Select: Type of class to return
            acf_render_field_setting(
                $field,
                array(
                    'label'         => __( 'Return type', 'acf' ),
                    'type'          => 'select',
                    'name'          => 'class_output',
                    'optgroup'      => true,
                    'required'      => 0,
                    'default_value' => '',
                    'allow_null'    => 1,
                    'return_format' => 'value',
                    'choices'       => array(
                        'text'       => __( 'Text class', 'pilopress' ),
                        'background' => __( 'Background class', 'pilopress' ),
                        'border'     => __( 'Border class', 'pilopress' ),
                    ),
                )
            );

            // True/False: Add to editor values
            acf_render_field_setting(
                $field,
                array(
                    'label'         => __( 'Only show colors with "Add to editor" option checked?', 'pilopress' ),
                    'instructions'  => '',
                    'name'          => 'show_add_to_editor',
                    'type'          => 'true_false',
                    'ui'            => 1,
                    'default_value' => 1,
                )
            );
        }

        /**
         * Format value
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function format_value( $value, $post_id, $field ) {

            // Skip if no value
            if ( !$value ) {
                return $value;
            }

            // Get all font colors
            $choices = pip_get_colors();

            // Get return type
            $class_output = acf_maybe_get( $field, 'class_output' );

            $return = null;
            if ( is_array( $value ) ) {
                foreach ( $value as $item ) {
                    // Get selected option
                    $font_color = acf_maybe_get( $choices, $item );
                    $font_color = $font_color ? $font_color['class_name'] : $value;
                    if ( !$font_color ) {
                        continue;
                    }

                    // Build class
                    switch ( $class_output ) {
                        case 'text':
                            $font_color = 'text-' . $font_color;
                            break;
                        case 'background':
                            $font_color = 'bg-' . $font_color;
                            break;
                        case 'border':
                            $font_color = 'border-' . $font_color;
                            break;
                    }
                    $return[ $item ] = $font_color;
                }
            } else {
                // Get selected option
                $font_color = acf_maybe_get( $choices, $value );
                $font_color = $font_color ? $font_color['class_name'] : $value;
                if ( !$font_color ) {
                    return null;
                }

                // Build class
                switch ( $class_output ) {
                    case 'text':
                        $this->remove_prefix( $font_color );
                        $font_color = 'text-' . $font_color;
                        break;
                    case 'background':
                        $this->remove_prefix( $font_color );
                        $font_color = 'bg-' . $font_color;
                        break;
                    case 'border':
                        $this->remove_prefix( $font_color );
                        $font_color = 'border-' . $font_color;
                        break;
                }
                $return = $font_color;
            }

            return $return;
        }

        /**
         * Remove class prefix
         *
         * @param $text
         */
        private function remove_prefix( &$text ) {
            $text = str_replace( 'text-', '', $text );
            $text = str_replace( 'bg-', '', $text );
            $text = str_replace( 'border-', '', $text );
        }

    }

    acf_new_instance( 'PIP_Font_Color_Field' );

}

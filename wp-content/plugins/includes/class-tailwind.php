<?php

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PIP_Addon_Tailwind' ) ) {

    /**
     * Class PIP_Addon_Tailwind
     */
    class PIP_Addon_Tailwind {

        /**
         * PIP_Addon_Tailwind constructor.
         */
        public function __construct() {

            // Pilo'Press v0.4
            add_filter( 'acf/load_value/key=field_body_classes', array( $this, 'default_body_class' ), 20, 1 );
            add_filter( 'acf/load_value/name=pip_typography', array( $this, 'default_typography' ), 20, 1 );
            add_filter( 'acf/load_value/name=pip_simple_colors', array( $this, 'default_simple_colors' ), 20, 1 );
            add_filter( 'acf/load_value/name=pip_colors_shades', array( $this, 'default_color_variants' ), 20, 1 );
            add_filter( 'acf/load_value/name=pip_button', array( $this, 'default_buttons' ), 20, 1 );
            add_filter( 'acf/load_value/name=pip_container', array( $this, 'default_container' ), 20, 1 );
            add_filter( 'acf/load_value/name=pip_fonts', array( $this, 'default_font_families' ), 20, 1 );

            // PILO_TODO: Add more tests => check if site has styles values
            $has_tailwind_pre_config = get_option( 'pipaddon_tailwind_pre_config' );
            if ( $has_tailwind_pre_config === '1' ) {
                return;
            }

            update_option( 'pipaddon_tailwind_pre_config', '1' );

            // Default override colors
            update_field( 'override_colors', true, 'pip_styles_configuration' );

            // TailwindCSS - CSS - Base import
            update_field( 'add_base_import', true, 'pip_styles_configuration' );

            // TailwindCSS - CSS - Utilities import
            update_field( 'add_utilities_import', true, 'pip_styles_configuration' );

        }

        /**
         * Default font families
         *
         * @param $value
         *
         * @return string
         */
        public function default_font_families( $value ) {

            if ( $value ) {
                return $value;
            }

            $default_values = array(

                // Font primary
                array(
                    'acf_fc_layout'                   => 'google_font',
                    'field_layout_google_font_title'  => 'Font primary',
                    'field_google_font_name'          => 'Roboto',
                    'field_google_font_url'           => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap',
                    'field_google_font_enqueue'       => '1',
                    'field_google_font_class_name'    => 'primary',
                    'field_google_font_fallback'      => 'system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif',
                    'field_google_font_add_to_editor' => '1',
                ),

                // Font secondary
                array(
                    'acf_fc_layout'                   => 'google_font',
                    'field_layout_google_font_title'  => 'Font secondary',
                    'field_google_font_name'          => 'Merriweather',
                    'field_google_font_url'           => 'https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap',
                    'field_google_font_enqueue'       => '1',
                    'field_google_font_class_name'    => 'secondary',
                    'field_google_font_fallback'      => 'system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif',
                    'field_google_font_add_to_editor' => '1',
                ),

            );

            return $default_values;
        }

        /**
         * Body Classes
         *
         * @param $value
         *
         * @return string
         */
        public function default_body_class( $value ) {
            if ( $value ) {
                return $value;
            }

            return 'text-base text-black font-primary antialiased overflow-x-hidden';
        }

        /**
         * Typography
         *
         * @param $value
         *
         * @return array
         */
        public function default_typography( $value ) {

            // If value has been modified, return
            if ( $value ) {
                $first_text_style_row     = reset( $value );
                $first_text_style_classes = acf_maybe_get( $first_text_style_row, 'field_typography_classes', '' );
                if ( $first_text_style_classes ) {
                    return $value;
                }
            }

            // Add default values
            $new_values = array();
            for ( $i = 1; $i <= 6; $i ++ ) {
                $new_values[] = array(
                    'field_typography_label'            => __( 'Titre', 'pilopress' ) . ' ' . $i,
                    'field_typography_class_name'       => "h$i",
                    'field_typography_classes_to_apply' => '',
                );
            }

            // Set default heading values
            $new_values[0]['field_typography_classes'] = 'font-primary leading-tight font-semibold text-black text-4xl';
            $new_values[1]['field_typography_classes'] = 'font-primary leading-tight font-semibold text-black text-3xl';
            $new_values[2]['field_typography_classes'] = 'font-primary leading-tight font-semibold text-black text-2xl';
            $new_values[3]['field_typography_classes'] = 'font-primary leading-tight font-semibold text-black text-xl';
            $new_values[4]['field_typography_classes'] = 'font-primary leading-tight font-semibold text-black text-lg';
            $new_values[5]['field_typography_classes'] = 'font-primary leading-tight font-semibold text-black text-base';

            // Return default values
            return $new_values;
        }

        /**
         * Colors - Simple
         *
         * @param $value
         *
         * @return string[][]
         */
        public function default_simple_colors( $value ) {

            // If value has been modified, return
            if ( $value ) {
                $first_simple_color_row   = reset( $value );
                $first_simple_color_value = acf_maybe_get( $first_simple_color_row, 'field_simple_color_value', '' );
                if ( $first_simple_color_value ) {
                    return $value;
                }
            }

            // Add default values
            $new_values = array(
                array(
                    'field_simple_color_label'         => 'Transparente',
                    'field_simple_color_name'          => 'transparent',
                    'field_simple_color_value'         => 'transparent',
                    'field_simple_color_add_to_editor' => '',
                ),
                array(
                    'field_simple_color_label'         => 'Actuelle',
                    'field_simple_color_name'          => 'current',
                    'field_simple_color_value'         => 'currentColor',
                    'field_simple_color_add_to_editor' => '',
                ),
                array(
                    'field_simple_color_label'         => 'Noire',
                    'field_simple_color_name'          => 'black',
                    'field_simple_color_value'         => '#2E2B28',
                    'field_simple_color_add_to_editor' => '1',
                ),
                array(
                    'field_simple_color_label'         => 'Blanche',
                    'field_simple_color_name'          => 'white',
                    'field_simple_color_value'         => '#ffffff',
                    'field_simple_color_add_to_editor' => '1',
                ),
            );

            // Return default values
            return $new_values;
        }

        /**
         * Colors - Variants
         *
         * @param $value
         *
         * @return array[]
         */
        public function default_color_variants( $value ) {

            // If value has been modified, return value
            if ( $value ) {
                $first_color_variant_row = reset( $value );
                if ( !empty( $first_color_variant_row ) ) {
                    $first_color_variant_shades = acf_maybe_get( $first_color_variant_row, 'field_colors_shades_shades', array() );
                    if ( !empty( $first_color_variant_shades ) ) {
                        $first_shade       = reset( $first_color_variant_shades );
                        $first_shade_value = acf_maybe_get( $first_shade, 'field_shade_value', '' );
                        if ( $first_shade_value ) {
                            return $value;
                        }
                    }
                }
            }

            // Add default values
            $new_values = array(

                // Gray variants
                array(
                    'field_colors_shades_color_name' => 'gray',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Gris',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#71717A',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 100',
                            'field_shade_name'          => '100',
                            'field_shade_value'         => '#F4F4F5',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#E4E4E7',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 300',
                            'field_shade_name'          => '300',
                            'field_shade_value'         => '#D4D4D8',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 400',
                            'field_shade_name'          => '400',
                            'field_shade_value'         => '#A1A1AA',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#71717A',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 600',
                            'field_shade_name'          => '600',
                            'field_shade_value'         => '#52525B',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#3F3F46',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 800',
                            'field_shade_name'          => '800',
                            'field_shade_value'         => '#27272A',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Gris 900',
                            'field_shade_name'          => '900',
                            'field_shade_value'         => '#18181B',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

                // Primary variants
                array(
                    'field_colors_shades_color_name' => 'primary',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Primaire',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 100',
                            'field_shade_name'          => '100',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 300',
                            'field_shade_name'          => '300',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 400',
                            'field_shade_name'          => '400',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 600',
                            'field_shade_name'          => '600',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 800',
                            'field_shade_name'          => '800',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Primaire 900',
                            'field_shade_name'          => '900',
                            'field_shade_value'         => '#1f2932',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

                // Secondary variants
                array(
                    'field_colors_shades_color_name' => 'secondary',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Secondaire',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 100',
                            'field_shade_name'          => '100',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 300',
                            'field_shade_name'          => '300',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 400',
                            'field_shade_name'          => '400',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 600',
                            'field_shade_name'          => '600',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 800',
                            'field_shade_name'          => '800',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Secondaire 900',
                            'field_shade_name'          => '900',
                            'field_shade_value'         => '#30b3df',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

                // Info variants
                array(
                    'field_colors_shades_color_name' => 'info',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Info',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#2b3c77',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Info 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#aab1c9',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Info 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#2b3c77',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Info 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#1a2447',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

                // Warning variants
                array(
                    'field_colors_shades_color_name' => 'warning',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Warning',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#ffc93e',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Warning 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#fefcbf',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Warning 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#ffc93e',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Warning 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#c07e1a',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

                // Error variants
                array(
                    'field_colors_shades_color_name' => 'error',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Error',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#e73434',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Error 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#fdfdfd',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Error 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#e73434',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Error 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#ae1616',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

                // Success variants
                array(
                    'field_colors_shades_color_name' => 'success',
                    'field_colors_shades_shades'     => array(
                        array(
                            'field_shade_label'         => 'Success',
                            'field_shade_name'          => 'DEFAULT',
                            'field_shade_value'         => '#2fbe2c',
                            'field_shade_add_to_editor' => '',
                        ),
                        array(
                            'field_shade_label'         => 'Success 200',
                            'field_shade_name'          => '200',
                            'field_shade_value'         => '#c9f3da',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Success 500',
                            'field_shade_name'          => '500',
                            'field_shade_value'         => '#2fbe2c',
                            'field_shade_add_to_editor' => '1',
                        ),
                        array(
                            'field_shade_label'         => 'Success 700',
                            'field_shade_name'          => '700',
                            'field_shade_value'         => '#067503',
                            'field_shade_add_to_editor' => '1',
                        ),
                    ),
                ),

            );

            // Return default values
            return $new_values;
        }

        /**
         * Buttons
         *
         * @param $value
         *
         * @return array[]
         */
        public function default_buttons( $value ) {

            // If value has been modified, return
            if ( $value ) {
                $first_button_row     = reset( $value );
                $first_button_classes = acf_maybe_get( $first_button_row, 'field_custom_button_classes', '' );
                if ( $first_button_classes ) {
                    return $value;
                }
            }

            // Add default values
            $new_values = array(
                array(
                    'field_custom_button_label'         => 'Bouton primaire',
                    'field_custom_button_class'         => 'btn-primary',
                    'field_custom_button_classes'       => 'relative transition-all duration-300 inline-flex items-center justify-center text-sm text-white uppercase px-4 py-2 leading-none font-primary font-bold bg-primary border-2 border-solid border-primary mr-2 mb-2',
                    'field_custom_button_add_to_editor' => '1',
                    'field_custom_button_states'        => array(
                        array(
                            'field_state_type'             => 'hover',
                            'field_state_classes_to_apply' => 'bg-primary-700 border-primary-700',
                        ),
                        array(
                            'field_state_type'             => 'active',
                            'field_state_classes_to_apply' => 'bg-primary-700 border-primary-700',
                        ),
                    ),
                ),
                array(
                    'field_custom_button_label'         => 'Lien menu',
                    'field_custom_button_class'         => 'link-menu',
                    'field_custom_button_classes'       => 'relative transition-all duration-300 inline-flex items-center justify-center text-white bg-transparent uppercase px-4 py-2 leading-none font-primary font-semibold border-b border-solid border-primary',
                    'field_custom_button_add_to_editor' => '1',
                    'field_custom_button_states'        => array(
                        array(
                            'field_state_type'             => 'hover',
                            'field_state_classes_to_apply' => 'text-primary-700 border-primary-700',
                        ),
                        array(
                            'field_state_type'             => 'active',
                            'field_state_classes_to_apply' => 'text-primary-700 border-primary-700',
                        ),
                    ),
                ),
            );

            // Return default values
            return $new_values;
        }

        /**
         * Container
         *
         * @param $value
         *
         * @return array
         */
        public function default_container( $value ) {

            // If value has been modified, return
            if ( $value ) {
                $first_container_padding_rows = acf_maybe_get( $value, 'field_container_padding_values', '' );
                if ( $first_container_padding_rows ) {
                    return $value;
                }
            }

            // Add default values
            $new_values = array(
                'field_container_center'            => '1',
                'field_container_add_padding_value' => '1',
                'field_container_padding_values'    => array(
                    array(
                        'field_container_padding_breakpoint' => 'DEFAULT',
                        'field_container_padding_value' => '1rem',
                    ),
                ),
            );

            // Return default values
            return $new_values;
        }

    }

    // Instantiate
    new PIP_Addon_Tailwind();
}

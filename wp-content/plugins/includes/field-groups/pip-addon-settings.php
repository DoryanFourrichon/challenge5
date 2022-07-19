<?php
if ( function_exists( 'acf_add_local_field_group' ) ) {

    acf_add_local_field_group(
        array(
            'key'                   => 'group_pip_addon_settings',
            'title'                 => 'API',
            'fields'                => array(
                array(
                    'key'               => 'field_gtm',
                    'label'             => '',
                    'name'              => 'gtm',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'acfe_permissions'  => '',
                    'default_value'     => '',
                    'placeholder'       => __( 'Google Tag Manager ID', 'pip-addon' ),
                    'prepend'           => '',
                    'append'            => '',
                    'maxlength'         => '',
                    'acfe_form'         => true,
                ),
                array(
                    'key'               => 'field_gmap',
                    'label'             => '',
                    'name'              => 'gmap',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'acfe_permissions'  => '',
                    'default_value'     => '',
                    'placeholder'       => __( 'Google Map API Key', 'pip-addon' ),
                    'prepend'           => '',
                    'append'            => '',
                    'maxlength'         => '',
                    'acfe_form'         => true,
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'pip_addon_settings',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
            'active'                => true,
            'description'           => '',
            'acfe_display_title'    => '',
            'acfe_autosync'         => array(
                0 => 'json',
            ),
            'acfe_permissions'      => '',
            'acfe_form'             => 1,
            'acfe_meta'             => '',
            'acfe_note'             => '',
            'acfe_categories'       => array(
                'pilopress' => 'Pilo\'Press',
            ),
        )
    );

}

<?php

/**
 * Pagination
 *
 * @param array $args
 * @param string $page_range
 * @param string $paged
 * @param string $custom_query
 */
function pip_pagination( $args = array(), $page_range = '', $paged = '', $custom_query = '' ) {

    // Deprecated argument "num_pages"
    if ( !is_array( $args ) ) {
        _deprecated_argument( __FUNCTION__, '0.2.0', __( 'Use first argument "$args" instead.', 'pilot-in' ) );
    }

    $args = wp_parse_args(
        $args,
        array(
            'page_range' => $page_range,
            'paged'      => $paged,
            'query'      => $custom_query,
        )
    );

    // Deprecated argument "page_range"
    if ( !empty( $page_range ) ) {
        _deprecated_argument( __FUNCTION__, '0.2.0', __( 'Use first argument "$args" instead.', 'pilot-in' ) );
    }

    // Deprecated argument "paged"
    if ( !empty( $paged ) ) {
        _deprecated_argument( __FUNCTION__, '0.2.0', __( 'Use first argument "$args" instead.', 'pilot-in' ) );
    }

    // Deprecated argument "custom_query"
    if ( !empty( $custom_query ) ) {
        _deprecated_argument( __FUNCTION__, '0.2.0', __( 'Use first argument "$args" instead.', 'pilot-in' ) );
    }

    $page_range   = acf_maybe_get( $args, 'page_range' );
    $paged        = acf_maybe_get( $args, 'paged' );
    $custom_query = acf_maybe_get( $args, 'query' );
    $num_pages    = acf_maybe_get( $args, 'num_pages' );

    // Set page_range if empty
    $page_range = $page_range ? $page_range : 2;

    // Set paged if empty
    global $paged;
    $paged = $paged ? $paged : 1; // phpcs:ignore

    // Set num_pages
    global $wp_query;
    $query = $custom_query ? $custom_query : $wp_query;

    $num_pages = ( $num_pages ? $num_pages : isset( $query->max_num_pages ) ) ? $query->max_num_pages : 1;

    // Get paginate links
    $big                = 999999999; // need an unlikely integer
    $pagination_numbers = paginate_links(
        array(
            'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'       => '?paged=%#%',
            'total'        => $num_pages,
            'current'      => $paged,
            'show_all'     => false,
            'end_size'     => 1,
            'mid_size'     => $page_range,
            'prev_next'    => false,
            'type'         => 'plain',
            'add_args'     => false,
            'add_fragment' => '',
        )
    );

    // If no paginate links, return
    if ( !$pagination_numbers ) {
        return;
    }

    $pagination_prev_data = array(
        'pagination_title' => __( 'Page précédente', 'pilot-in' ),
        'icon_classes'     => 'far fa-sm fa-arrow-left mr-1',
    );

    $pagination_next_data = array(
        'pagination_title' => __( 'Page suivante', 'pilot-in' ),
        'icon_classes'     => 'far fa-sm fa-arrow-right ml-1',
    );

    $pagination_prev_data           = apply_filters( 'pip_addon/pagination/prev', $pagination_prev_data );
    $pagination_next_data           = apply_filters( 'pip_addon/pagination/next', $pagination_next_data );
    $pagination_hide_on_extremities = apply_filters( 'pip_addon/pagination/hide_on_extremities', true );

    // Display pagination links
    ob_start(); ?>
    <div class="pagination relative flex items-center justify-center w-full">

        <?php // Previous page link ?>
        <a
            class="hidden mr-auto pagination-previous <?php echo( $paged > 1 ? 'md:block' : ( $pagination_hide_on_extremities ? '' : 'md:block opacity-25 pointer-events-none' ) ); ?>"
            href="<?php echo get_pagenum_link( $paged - 1 ); ?>">
            <span class="<?php echo acf_maybe_get( $pagination_prev_data, 'icon_classes' ); ?>"></span>
            <?php echo acf_maybe_get( $pagination_prev_data, 'pagination_title' ); ?>
        </a>

        <?php // Numbers ?>
        <div class="pagination-numbers absolute inset-auto">
            <?php echo $pagination_numbers; ?>
        </div>

        <?php // Next page link ?>
        <a
            class="hidden ml-auto pagination-next <?php echo( $paged < $num_pages ? 'md:block' : ( $pagination_hide_on_extremities ? '' : 'md:block opacity-25 pointer-events-none' ) ); ?>"
            href="<?php echo get_pagenum_link( $paged + 1 ); ?>">
            <?php echo acf_maybe_get( $pagination_next_data, 'pagination_title' ); ?>
            <span class="<?php echo acf_maybe_get( $pagination_next_data, 'icon_classes' ); ?>"></span>
        </a>

    </div>
    <?php
    echo ob_get_clean();
}

/**
 *  Retrieve layouts based on given "acf_fc_layout" in the pip_flexible of given post
 *
 * @param mixed  $layouts string or array of strings of the layouts' "acf_fc_layout"
 * @param string $post_id
 *
 * @return mixed false if no layouts were found, if found an array of layouts
 */
function pip_get_flexible_layout( $layouts, $post_id = '' ) {

    $response = false;

    if ( !$layouts ) {
        return $response;
    }

    $pip_flexible      = acf_get_instance( 'PIP_Flexible' );
    $pip_flexible_name = (string) $pip_flexible->flexible_field_name;
    $post_id           = $post_id ? $post_id : get_the_ID();
    $pip_flexible      = get_field( $pip_flexible_name, $post_id );

    if ( !$pip_flexible ) {
        return $response;
    }

    if ( !is_array( $layouts ) ) {
        $layouts = array( $layouts );
    }

    $found_layouts = array();
    foreach ( $pip_flexible as $position => $layout ) {
        $layout_name = pip_maybe_get( $layout, 'acf_fc_layout' );

        if ( in_array( $layout_name, $layouts, true ) ) {
            $found_layouts[ $position ] = pip_maybe_get( $pip_flexible, $position );
        }
    }

    if ( !empty( $found_layouts ) ) {
        $response = $found_layouts;
    }

    return $response;

}

/**
 *  Flatten a multidimensional array
 *
 * @param $array
 *
 * @return array|false
 */
function array_flatten_recursive( $array ) {

    if ( !$array ) {
        return false;
    }

    $flat = array();
    $rii  = new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) );

    foreach ( $rii as $value ) {
        $flat[] = $value;
    }

    return $flat;
}

/**
 *  PIP - Get Sized Image URL - Useful for getting sized URL in one line (most useful case with ACF Image)
 *
 * @param mixed  $img  image array or image ID
 * @param string $size image size
 *
 * @return string|null URL of the sized image
 *
 *  Example of use case : echo pip_get_sized_image_url( get_sub_field('img'), 'full' )
 */
function pip_get_sized_image_url( $img, $size = 'thumbnail' ) {
    if ( empty( $img ) ) {
        return null;
    }

    if ( is_array( $img ) ) {
        $img = pip_maybe_get( $img, 'ID' );
    }

    $attachment = wp_get_attachment_image_src( $img, $size );

    return reset( $attachment );
}

/**
 * Check if current language is RTL or LTR
 *
 * @return bool
 */
function pip_is_rtl() {
    if ( !function_exists( 'pll_current_language' ) ) {
        return false;
    }

    $current_language = pll_current_language( 'OBJECT' );

    return $current_language ? (bool) $current_language->is_rtl : false;
}

/**
 * Get layout configuration data
 *
 * @param string|null $layout_name
 *
 * @return array
 */
function pip_layout_configuration( $layout_name = null ) {

    // Get layout name
    $layout_object = (array) get_sub_field_object( 'layout_settings' );
    if ( pip_maybe_get( $layout_object, 'parent_layout' ) ) {
        $layout_name = pip_maybe_get( $layout_object, 'parent_layout' );
        $layout_name = $layout_name ? str_replace( 'layout_', '', $layout_name ) : '';
    }

    // Get layout vars
    $field_group = PIP_Layouts_Single::get_layout_field_group_by_slug( $layout_name );
    $layout_vars = acf_maybe_get( $field_group, 'pip_layout_var' );
    $css_vars    = array();
    if ( $layout_vars ) {
        foreach ( $layout_vars as $layout_var ) {
            $css_vars[ acf_maybe_get( $layout_var, 'pip_layout_var_key' ) ] = acf_maybe_get( $layout_var, 'pip_layout_var_value' );
        }
    }

    // Get configuration data
    $configuration = (array) get_sub_field( 'layout_settings' );
    $bg_color      = pip_maybe_get( $configuration, 'bg_color' );

    // Section vertical spacing
    $vertical_space_desktop = pip_maybe_get( $configuration, 'vertical_space' );
    $vertical_space_tablet  = pip_maybe_get( $configuration, 'vertical_space_tablet' );
    $vertical_space_mobile  = pip_maybe_get( $configuration, 'vertical_space_mobile' );

    $vertical_space  = $vertical_space_mobile ? " $vertical_space_mobile" : '';
    $vertical_space .= $vertical_space_tablet ? " md:$vertical_space_tablet" : '';
    $vertical_space .= " lg:$vertical_space_desktop";

    // Section id + classes
    $section_id_val = pip_maybe_get( $configuration, 'section_id' ) ? pip_maybe_get( $configuration, 'section_id' ) : acf_uniqid( $layout_name );
    $section_id     = $section_id_val ? 'id="' . $section_id_val . '"' : '';
    $section_class  = "$layout_name relative w-full $bg_color $vertical_space";

    // Return layout configuration data
    return apply_filters(
        'pip_addon/layout/config',
        array(
            'layout_name'    => $layout_name,
            'section_class'  => $section_class,
            'section_id'     => $section_id,
            'bg_color'       => $bg_color,
            'vertical_space' => $vertical_space,
            'css_vars'       => $css_vars,
        ),
        $layout_object,
        $layout_name
    );
}

/**
 * Display a version of the website's Logo
 *
 * @param string|null $version (empty for WordPress custom_logo or slug of the version added through pip_addon/logo_versions hook)
 *
 * Example pip_the_logo('logo-white') to get the logo which get_theme_mod is 'logo-white'
 * (see pip_add_logo_versions_to_customizer() to add versions)
 *
 * @return false
 */
function pip_the_logo( $version = '' ) {

    $logo_url = false;

    // If custom_logo exist then it is the default logo
    if ( !has_custom_logo() ) {
        return false;
    }

    $logo_id = get_theme_mod( 'custom_logo' );
    if ( !$logo_id ) {
        return false;
    }

    $logo = wp_get_attachment_image_src( $logo_id, 'full' );
    if ( is_array( $logo ) && !empty( $logo ) ) {
        $logo_url = reset( $logo );
    }

    if ( !empty( $version ) ) {

        // If there is a logo version with an image then override the default logo with the versioned logo
        $logo_version = get_theme_mod( $version );
        if ( $logo_version ) {
            $logo_url = $logo_version;
        }
    }

    $logo_class = 'pip-logo-link';
    if ( $version ) {
        $logo_class .= ' logo-' . $version;
    }

    $logo_alt = get_bloginfo( 'name', 'display' );
    $logo_id  = get_theme_mod( 'custom_logo' );

    if ( $logo_id ) {
        $alt = get_post_meta( $logo_id, '_wp_attachment_image_alt', true );
        if ( !empty( $alt ) ) {
            $logo_alt = $alt;
        }
    }

    ?>

    <a
            class="<?php echo esc_attr( $logo_class ); ?>"
            href="<?php echo esc_url( home_url( '/' ) ); ?>"
            rel="home"
            itemprop="url">
        <img
                src="<?php echo esc_url( $logo_url ); ?>"
                alt="<?php echo esc_attr( $logo_alt ); ?>"/>
    </a>

    <?php
}

if ( !function_exists( 'get_layout_title' ) ) {
    /**
     *  This function will return a string representation of the current layout title within a 'have_rows' loop
     *
     * @return string
     */
    function get_layout_title() {
        $layout_title = false;

        // Get row
        $row = get_row();
        if ( !$row ) {
            return $layout_title;
        }

        // Browse row
        foreach ( $row as $key => $value ) {
            // If no title, skip
            if ( mb_stripos( $key, '_title' ) === false ) {
                continue;
            }

            // Store value
            $layout_title = $value;
        }

        // Return title
        return $layout_title;

    }
}

/**
 *  Helper to upload a remote file (not only images) to the WP media library
 *  (fork of "media_sideload_image")
 *
 *  Example for setting post thumbnail from img URL:
 *  $upload_img_id = pip_upload_file( $url_img_file, $wp_post_id, null, 'id' );
 *  set_post_thumbnail( $wp_post_id, $upload_img_id );
 *
 * @param string  $file
 * @param integer $post_id
 * @param string  $desc
 * @param string  $return
 *
 * @return bool|int|string|WP_Error
 */
function pip_upload_file( $file = '', $post_id = 0, $desc = null, $return = 'src' ) {

    // Add admin required files
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if ( !empty( $file ) ) {

        $file_array         = array();
        $file_array['name'] = wp_basename( $file );

        // Download file to temp location.
        $file_array['tmp_name'] = download_url( $file );

        // If error storing temporarily, return the error.
        if ( is_wp_error( $file_array['tmp_name'] ) ) {
            return $file_array['tmp_name'];
        }

        // Do the validation and storage stuff.
        $id = media_handle_sideload( $file_array, $post_id, $desc );

        // If error storing permanently, unlink.
        if ( is_wp_error( $id ) ) {
            @unlink( $file_array['tmp_name'] );

            return $id;
            // If attachment id was requested, return it early.
        } elseif ( $return === 'id' ) {
            return $id;
        }

        $src = wp_get_attachment_url( $id );
    }

    // Finally, check to make sure the file has been saved, then return the HTML.
    if ( !empty( $src ) ) {
        if ( $return === 'src' ) {
            return $src;
        }
    } else {
        return new WP_Error( 'image_sideload_failed' );
    }
}

/**
 * Get responsive class
 *
 * @param      $container_width
 * @param bool $advanced_mode
 * @param null $class_prefix
 *
 * @return string
 */
function pip_get_responsive_class( $container_width, $advanced_mode = false, $class_prefix = null ) {
    $content_width = '';

    // If no container width, return
    if ( !$container_width ) {
        return $content_width;
    }

    // Browse container widths
    foreach ( $container_width as $screen => $nb_items ) {
        if ( $advanced_mode ) {
            if ( !strstr( $screen, '_advanced' ) ) {
                continue;
            }
        } else {
            if ( strstr( $screen, '_advanced' ) ) {
                continue;
            }
        }

        // Remove "_advanced" from screen size
        $screen = str_replace( '_advanced', '', $screen );

        // Build responsive class
        switch ( $screen ) {
            case 'default':
                $content_width .= ' ' . $class_prefix . $nb_items;
                break;
            default:
                $content_width .= ' ' . $screen . ':' . $class_prefix . $nb_items;
                break;
        }
    }

    return $content_width;
}

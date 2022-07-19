<?php
get_header();

if ( function_exists( 'the_pip_content' ) ) {

    // Use term flexible if it exists otherwise fallback on archive flexible
    $term_content = has_flexible( 'pip_flexible', pip_get_formatted_post_id() );
    if ( $term_content ) {

        the_pip_content();

    } else {

        $current_term       = get_queried_object();
        $current_taxo_name  = pip_maybe_get( $current_term, 'taxonomy' );
        $current_taxo       = $current_taxo_name ? get_taxonomy( $current_taxo_name ) : '';
        $posts_type_related = pip_maybe_get( $current_taxo, 'object_type' );
        $post_type_related  = !empty( $posts_type_related ) ? acf_unarray( $posts_type_related ) : false;

        // Attempts to get the related post-type archive
        // $post_type_related = get_post_type();
        if ( !$post_type_related ) {

            global $wp_query;
            $current_query     = pip_maybe_get( $wp_query, 'query' );
            $post_type_related = pip_maybe_get( $current_query, 'post_type' );
            if ( !$post_type_related ) {
                $post_type_related = !empty( $posts_type_related ) ? reset( $posts_type_related ) : '';
            }

        }

        // If 3rd party want to choose which archive post-type should be used
        $post_type_related = apply_filters( 'pip_tax_post_type', $post_type_related, $posts_type_related, $current_taxo );

        switch ( $post_type_related ) {
            case 'post':
                $archive_id = get_option( 'page_for_posts' );
                break;

            case 'product':
                $archive_id = wc_get_page_id( 'shop' );
                break;

            default:
                $archive_id = $post_type_related . '_archive';
                break;
        }

        the_pip_content( $archive_id );

    }
}

get_footer();

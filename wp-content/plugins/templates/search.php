<?php
get_header();

if ( function_exists( 'get_pip_header' ) && !have_posts() ) {
    get_pip_header();
}

if ( function_exists( 'the_pip_content' ) ) {

    /** Display correct archive for search if we have posts */
    if ( have_posts() ) :

        $post_type_related = get_post_type();
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

        /** Display no results template */
    else : ?>
<section class="text-center p-16">
    <h1 class="h3">
        <?php _e( 'Vous avez cherché : ', 'pilot-in' ); ?>
        <span class="text-secondary"><?php echo get_search_query(); ?></span>
    </h1>
    <p class="mt-8">
        <?php _e( 'Aucun résultat trouvé.', 'pilot-in' ); ?>
    </p>
    <a class="text-secondary inline-block mt-2" href="<?php echo home_url(); ?>">
        <?php _e( 'Revenir à la page d\'accueil', 'pilot-in' ); ?>
    </a>
</section>
        <?php
    endif;
}

if ( function_exists( 'get_pip_footer' ) && !have_posts() ) {
    get_pip_footer();
}
get_footer();

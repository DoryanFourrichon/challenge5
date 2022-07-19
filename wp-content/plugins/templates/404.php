<?php
get_header();
if ( function_exists( 'get_pip_header' ) ) {
    get_pip_header();
}
?>
<section class="text-center p-16">
    <h1>
        <?php _e( 'Erreur 404', 'pilot-in' ); ?>
    </h1>
    <h5><?php _e( 'Page introuvable', 'pilot-in' ); ?></h5>
    <a class="text-secondary inline-block mt-6" href="<?php echo home_url(); ?>">
        <?php _e( 'Revenir à la page d\'accueil', 'pilot-in' ); ?>
    </a>
</section>
<?php
if ( function_exists( 'get_pip_footer' ) ) {
    get_pip_footer();
}
get_footer();

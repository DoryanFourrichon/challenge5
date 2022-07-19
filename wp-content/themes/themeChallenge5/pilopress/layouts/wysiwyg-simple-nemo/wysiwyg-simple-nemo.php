<?php
// Section
$configuration = pip_layout_configuration();
$css_vars      = acf_maybe_get( $configuration, 'css_vars' );

// CSS Vars
$content_vars = acf_maybe_get( $css_vars, 'content-intro' );

// Fields
$content = get_sub_field( 'content' );

// Configuration
$advanced_mode   = get_sub_field( 'advanced_mode' );
$container_width = get_sub_field( 'container_width' );

// Content width
$content_width = pip_get_responsive_class( $container_width, $advanced_mode );
?>
    <section <?php echo acf_maybe_get( $configuration, 'section_id' ); ?>
        class="<?php echo acf_maybe_get( $configuration, 'section_class' ); ?>">
        <div class="container">
            <div class="mx-auto <?php echo $content_width; ?>">

                <?php if ( $content ) : ?>
                    <div class="content <?php echo $content_vars; ?>">
                        <?php echo $content; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
<?php

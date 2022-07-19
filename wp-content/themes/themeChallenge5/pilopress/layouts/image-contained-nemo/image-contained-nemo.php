<?php
// Section
$configuration = pip_layout_configuration();
$css_vars      = acf_maybe_get( $configuration, 'css_vars' );

// CSS Vars
$wrapper_image_vars = acf_maybe_get( $css_vars, 'wrapper-image' );
$image_vars         = acf_maybe_get( $css_vars, 'image' );

// Fields
$image = get_sub_field( 'image' );

// Configuration
$advanced_mode   = get_sub_field( 'advanced_mode' );
$container_width = get_sub_field( 'container_width' );
$img_height      = get_sub_field( 'img_height' );

// Content width
$content_width = pip_get_responsive_class( $container_width, $advanced_mode );
?>
    <section <?php echo acf_maybe_get( $configuration, 'section_id' ); ?>
            class="<?php echo acf_maybe_get( $configuration, 'section_class' ); ?>">
        <div class="container">
            <div class="mx-auto wrapper-image <?php echo $content_width . ' ' . $wrapper_image_vars; ?>">

                <img
                        src="<?php echo acf_maybe_get( $image, 'url' ); ?>"
                        alt="<?php echo acf_maybe_get( $image, 'alt' ); ?>"
                        class="image <?php echo $image_vars; ?>"
                        style="height: <?php echo $img_height; ?>px">

            </div>
        </div>
    </section>
<?php

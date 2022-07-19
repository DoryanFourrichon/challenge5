<?php
// Section
$configuration = pip_layout_configuration();
$css_vars      = acf_maybe_get( $configuration, 'css_vars' );

// Fields
$section_intro = get_sub_field( 'section_intro' );
$section_end   = get_sub_field( 'section_end' );

// Configuration
$advanced_mode   = get_sub_field( 'advanced_mode' );
$container_width = get_sub_field( 'container_width' );

// Content width
$content_width = pip_get_responsive_class( $container_width, $advanced_mode );
?>
<section <?php echo acf_maybe_get( $configuration, 'section_id' ); ?>
    class="<?php echo acf_maybe_get( $configuration, 'section_class' ); ?>"
    style="<?php echo apply_filters( 'pip/layout/section_style', '', $configuration ); ?>"
    <?php echo apply_filters( 'pip/layout/section_attributes', '', $configuration ); ?>>

    <?php // To add dynamic markup at the beginning of this layout
    do_action( 'pip/layout/section_start', $configuration ); ?>

    <div class="container">
        <div class="mx-auto <?php echo $content_width; ?>">

            <?php
            // Intro
            if ( $section_intro ) : ?>
                <div class="section_intro <?php echo acf_maybe_get( $css_vars, 'section_intro' ); ?>">
                    <?php echo $section_intro; ?>
                </div>
            <?php endif; ?>

            <?php
            // Custom content here
            ?>

            <?php 
            // Outro
            if ( $section_end ) : ?>
                <div class="section_end <?php echo acf_maybe_get( $css_vars, 'section_end' ); ?>">
                    <?php echo $section_end; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php // To add dynamic markup at the end of this layout
    do_action( 'pip/layout/section_end', $configuration ); ?>

</section>
<?php

<?php
// Section
$configuration = pip_layout_configuration();
$css_vars      = acf_maybe_get( $configuration, 'css_vars' );

// CSS Vars
$wrapper_cols_vars = acf_maybe_get( $css_vars, 'wrapper_cols' );
$wrapper_col_vars  = acf_maybe_get( $css_vars, 'wrapper_col' );

// Fields
$columns = get_sub_field( 'columns' );

// Configuration
$advanced_mode        = get_sub_field( 'advanced_mode' );
$container_width      = get_sub_field( 'container_width' );
$placement            = get_sub_field( 'placement' );
$horizontal_alignment = get_sub_field( 'horizontal_alignment' );
$vertical_alignment   = get_sub_field( 'vertical_alignment' );

// Content width
$content_width = pip_get_responsive_class( $container_width, $advanced_mode );

// Get columns configuration
$col_classes = wysiwyg_nemo_get_cols_config( $placement, $advanced_mode );
$nb_cols     = wysiwyg_nemo_get_cols_config( $placement, $advanced_mode, 'nb_cols' );
?>
    <section <?php echo acf_maybe_get( $configuration, 'section_id' ); ?>
            class="<?php echo acf_maybe_get( $configuration, 'section_class' ); ?>">
        <div class="container">
            <div class="mx-auto <?php echo $content_width; ?>">

                <?php if ( have_rows( 'columns' ) ) : ?>
                    <div class="wrapper_cols flex flex-wrap <?php echo $wrapper_cols_vars . ' ' . $horizontal_alignment . ' ' . $vertical_alignment; ?>">

                        <?php $col_index = 0; ?>
                        <?php while ( have_rows( 'columns' ) ) : ?>
                            <?php the_row(); ?>
                            <?php $col_size = $nb_cols > 1 ? $col_classes[ $col_index % $nb_cols ] : $placement; ?>

                            <div class="wrapper_col <?php echo $wrapper_col_vars . ' ' . $col_size; ?>">
                                <?php echo get_sub_field( 'content' ); ?>
                            </div>

                            <?php $col_index ++; ?>
                        <?php endwhile; ?>

                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
<?php

<?php

/**
 * Get columns configuration
 *
 * @param        $placement
 * @param        $advanced_mode
 * @param string $return
 *
 * @return array|int
 */
function wysiwyg_nemo_get_cols_config( $placement, $advanced_mode, $return = 'col_classes' ) {
    $col_classes = array();
    $placements  = array();

    // Browse all placements
    foreach ( $placement as $screen => $classes ) {
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

        // Prefix classes with screen
        $classes = explode( ' ', $classes );
        foreach ( $classes as &$class ) {
            $class = $screen !== 'default' ? $screen . ':' . $class : $class;
        }
        $placements[] = $classes;
    }

    // Count columns
    $nb_cols = count( $placements );

    // Reformat array
    for ( $i = 0; $i <= 5; $i ++ ) {
        if ( !pip_maybe_get( $placements, $i ) ) {
            continue;
        }

        for ( $j = 0; $j < $nb_cols; $j ++ ) {

            if ( pip_maybe_get( $placements[ $i ], $j ) ) {
                $col_classes[ $j ][] = pip_maybe_get( $placements[ $i ], $j );
            } else {
                $col_classes[ $j ][] = $placements[ $i ][0];
            }
        }
    }

    // Implode classes
    foreach ( $col_classes as &$item ) {
        $item = implode( ' ', $item );
    }

    // Return
    switch ( $return ) {
        default:
        case 'col_classes':
            return $col_classes;
        case 'nb_cols':
            return $nb_cols;
    }
}

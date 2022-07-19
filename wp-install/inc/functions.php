<?php

if ( !function_exists( '_' ) ) {
    function _( $str ) {
        echo $str;
    }
}

function sanit( $str ) {
    return addcslashes( str_replace( array( ';', "\n" ), '', $str ), '\\' );
}

function rrmdir( $src ) {
    $dir = opendir( $src );
    while ( false !== ( $file = readdir( $dir ) ) ) {
        if ( ( $file != '.' ) && ( $file != '..' ) ) {
            $full = $src . '/' . $file;
            if ( is_dir( $full ) ) {
                rrmdir( $full );
            } else {
                unlink( $full );
            }
        }
    }
    closedir( $dir );
    rmdir( $src );
}

// _print_r()
function _print_r( $data, $args = false ) {
    $args = wp_parse_args(
        $args,
        array(
            'title'  => false,
            'return' => false,
            'dump'   => false,
            'pre'    => true,
        )
    );

    $code = print_r( $data, true );
    if ( $args['dump'] ) {
        ob_start();
            var_dump( $data );
        $code = ob_get_clean();
    }

    $title = false;
    if ( $args['title'] ) {
        $title = '<h5><strong>' . $args['title'] . '</strong></h5><hr>';
    }

    $pre_start = $pre_end = false;
    if ( $args['pre'] ) {
        $pre_start = "<pre style='position:relative; z-index:1000000;'>";
        $pre_end   = '</pre>';
    }

    $return = $pre_start . $title . $code . $pre_end;

    if ( $args['return'] ) {
        return $return;
    }

    echo $return;
}

function glob_recursive( $directory, &$directories = array() ) {
    foreach ( glob( $directory, GLOB_ONLYDIR | GLOB_NOSORT ) as $folder ) {
        $directories[] = $folder;
        glob_recursive( "{$folder}/*", $directories );
    }
}

function find_files( $directory, $extensions = array() ) {

    glob_recursive( $directory, $directories );
    $files = array();

    foreach ( $directories as $directory ) {
        foreach ( $extensions as $extension ) {
            foreach ( glob( "{$directory}/*.{$extension}" ) as $file ) {
                $files[ $extension ][] = $file;
            }
        }
    }

    return $files;
}

function find_dot_files( $directory, $extensions = array( '.gitignore', '.gitkeep' ) ) {

    glob_recursive( $directory, $directories );
    $files = array();
    if ( empty( $directories ) ) {
        return $files;
    }

    foreach ( $directories as $directory ) {
        foreach ( $extensions as $extension ) {
            foreach ( glob( "{$directory}/{$extension}" ) as $file ) {
                $files[ $extension ][] = $file;
            }
        }
    }

    return $files;
}

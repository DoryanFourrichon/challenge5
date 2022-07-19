<?php
/*
Script Name: PiloPress Installer
Author: Pilot-in
Script URI: https://github.com/Pilot-in/PiloPress-Installer
Version: 1.2
Licence: GPLv3
*/
// phpcs:ignoreFile
@set_time_limit( 0 );

define( 'WP_API_CORE', 'http://api.wordpress.org/core/version-check/1.7/?locale=' );
define( 'WPQI_PREMIUM_PLUGINS_PATH', 'plugins/' );
define( 'WPQI_CACHE_PATH', 'cache/' );
define( 'WPQI_CACHE_CORE_PATH', WPQI_CACHE_PATH . 'core/' );
define( 'WPQI_CACHE_PLUGINS_PATH', WPQI_CACHE_PATH . 'plugins/' );

require 'inc/functions.php';

// Force URL with index.php
$last_part_url = explode( '/', trim( $_SERVER['REQUEST_URI'], '/' ) );
if ( empty( $_GET ) && end( $last_part_url ) == 'wp-install' ) {
    header( 'Location: index.php' );
    die();
}

// Create cache directories
if ( !is_dir( WPQI_CACHE_PATH ) ) {
    mkdir( WPQI_CACHE_PATH );
}
if ( !is_dir( WPQI_CACHE_CORE_PATH ) ) {
    mkdir( WPQI_CACHE_CORE_PATH );
}
if ( !is_dir( WPQI_CACHE_PLUGINS_PATH ) ) {
    mkdir( WPQI_CACHE_PLUGINS_PATH );
}

// We verify if there is a preconfig file
$data = array();
if ( file_exists( 'data.ini' ) ) {
    $data = json_encode( parse_ini_file( 'data.ini' ) );
}

// We add  ../ to directory
$directory = !empty( $_POST['directory'] ) ? '../' . $_POST['directory'] . '/' : '../';

if ( isset( $_GET['action'] ) ) {

    switch ( $_GET['action'] ) {

        case 'check_before_upload':
            $data = array();

            // We verify if we can connect to DB or WP is not installed yet

            // DB Test
            try {
                $db = new PDO( 'mysql:host=' . $_POST['dbhost'] . ';dbname=' . $_POST['dbname'] . ';port=' . $_POST['dbport'], $_POST['uname'], $_POST['pwd'] );

                $remove_default_content = $_POST['default_content'] ?? '1';
                if ( $remove_default_content == '1' ) {

                    $tables = $db->query( 'SHOW TABLES' );
                    while ( $row = $tables->fetch( PDO::FETCH_NUM ) ) {
                        $tables_list[] = $row[0];
                    }

                    if ( !empty( $tables_list ) ) {
                        foreach ( $tables_list as $table ) {
                            $sql = $db->query( "DROP TABLE $table" );
                        }
                    }
                }
            } catch ( Exception $e ) {
                $data['db'] = 'error etablishing connection';
            }

            // WordPress test
            if ( file_exists( $directory . 'wp-config.php' ) ) {
                $data['wp'] = 'error directory';
            }

            // We send the response
            echo json_encode( $data );

            break;

        case 'download_wp':
            // Get WordPress language
            $language = substr( $_POST['language'], 0, 6 );

            // Get WordPress data
            $wp = json_decode( file_get_contents( WP_API_CORE . $language ) )->offers[0];

            // We download the latest version of WordPress

            if ( !file_exists( WPQI_CACHE_CORE_PATH . 'wordpress-' . $wp->version . '-' . $language . '.zip' ) ) {
                file_put_contents( WPQI_CACHE_CORE_PATH . 'wordpress-' . $wp->version . '-' . $language . '.zip', file_get_contents( $wp->download ) );
            }

            break;

        case 'unzip_wp':
            // Get WordPress language
            $language = substr( $_POST['language'], 0, 6 );

            // Get WordPress data
            $wp = json_decode( file_get_contents( WP_API_CORE . $language ) )->offers[0];

            // We create the website folder with the files and the WordPress folder

            // If we want to put WordPress in a subfolder we create it
            if ( !empty( $directory ) ) {
                if ( mkdir( $directory ) ) {
                    chmod( $directory, 0755 );
                }
            }

            $zip = new ZipArchive();

            // We verify if we can use the archive
            if ( $zip->open( WPQI_CACHE_CORE_PATH . 'wordpress-' . $wp->version . '-' . $language . '.zip' ) === true ) {

                // Let's unzip
                $zip->extractTo( '.' );
                $zip->close();

                // We scan the folder
                $files = scandir( 'WordPress' );

                // We remove the "." and ".." from the current folder and its parent
                $files = array_diff( $files, array( '.', '..' ) );

                // We move the files and folders
                foreach ( $files as $file ) {
                    rename( 'wordpress/' . $file, $directory . '/' . $file );
                }

                rmdir( 'WordPress' );                                   // We remove WordPress folder
                unlink( $directory . '/license.txt' );                  // We remove licence.txt
                unlink( $directory . '/readme.html' );                  // We remove readme.html
                unlink( $directory . '/wp-content/plugins/hello.php' ); // We remove Hello Dolly plugin
            }

            break;

        case 'wp_config':
            // Let's create the wp-config file

            // We retrieve each line as an array
            $config_file = file( $directory . 'wp-config-sample.php' );

            // Managing the security keys
            $secret_keys = explode( "\n", file_get_contents( 'https://api.wordpress.org/secret-key/1.1/salt/' ) );

            foreach ( $secret_keys as $k => $v ) {
                $secret_keys[ $k ] = substr( $v, 28, 64 );
            }

            // We change the data
            $key = 0;
            foreach ( $config_file as &$line ) {

                if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
                    $line = '$table_prefix  = \'' . sanit( $_POST['prefix'] ) . "';\r\n";
                    continue;
                }

                if ( !preg_match( '/^define\( \'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
                    continue;
                }

                $constant = $match[1];

                switch ( $constant ) {
                    case 'WP_DEBUG':
                        // WP_DEBUG
                        if ( (int) $_POST['debug'] == 1 ) {
                            $line = "define('WP_DEBUG', true);\r\n";
                        }

                        // WP_DEBUG_DISPLAY
                        if ( (int) $_POST['debug_display'] == 1 ) {
                            $line .= "\r\n\n " . "/** Affichage des erreurs à l'écran */" . "\r\n";
                            $line .= "define('WP_DEBUG_DISPLAY', true);\r\n";
                        }

                        // WP_DEBUG_LOG
                        if ( (int) $_POST['debug_log'] == 1 ) {
                            $line .= "\r\n\n " . '/** Ecriture des erreurs dans un fichier log */' . "\r\n";
                            $line .= "define('WP_DEBUG_LOG', true);\r\n";
                        }

                        // We add the extras constant
                        if ( !empty( $_POST['uploads'] ) ) {
                            $line .= "\r\n\n " . '/** Dossier de destination des fichiers uploadés */' . "\r\n";
                            $line .= "define('UPLOADS', '" . sanit( $_POST['uploads'] ) . "');";
                        }

                        $line .= "\r\n\n " . "/** Limite à 5 les révisions d'articles */" . "\r\n";
                        $line .= "define('WP_POST_REVISIONS', 5);";

                        $line .= "\r\n\n " . "/** Désactivation de l'éditeur de thème et d'extension */" . "\r\n";
                        $line .= "define('DISALLOW_FILE_EDIT', true);";

                        $line .= "\r\n\n " . '/** Intervalle des sauvegardes automatique */' . "\r\n";
                        $line .= "define('AUTOSAVE_INTERVAL', 7200);";

                        $line .= "\r\n\n " . '/** On augmente la mémoire limite */' . "\r\n";
                        $line .= "define('WP_MEMORY_LIMIT', '256M');" . "\r\n";

                        $line .= "\r\n\n " . "/** On augmente la mémoire limite de l'admin */" . "\r\n";
                        $line .= "define('WP_MAX_MEMORY_LIMIT', '512M');" . "\r\n";

                        break;
                    case 'DB_NAME':
                        $line = "define('DB_NAME', '" . sanit( $_POST['dbname'] ) . "');\r\n";
                        break;
                    case 'DB_USER':
                        $line = "define('DB_USER', '" . sanit( $_POST['uname'] ) . "');\r\n";
                        break;
                    case 'DB_PASSWORD':
                        $line = "define('DB_PASSWORD', '" . sanit( $_POST['pwd'] ) . "');\r\n";
                        break;
                    case 'DB_HOST':
                        $line = "define('DB_HOST', '" . sanit( $_POST['dbhost'] ) . "');\r\n";
                        break;
                    case 'AUTH_KEY':
                    case 'SECURE_AUTH_KEY':
                    case 'LOGGED_IN_KEY':
                    case 'NONCE_KEY':
                    case 'AUTH_SALT':
                    case 'SECURE_AUTH_SALT':
                    case 'LOGGED_IN_SALT':
                    case 'NONCE_SALT':
                        $line = "define('" . $constant . "', '" . $secret_keys[ $key ++ ] . "');\r\n";
                        break;

                    case 'WPLANG':
                        $line = "define('WPLANG', '" . sanit( $_POST['language'] ) . "');\r\n";
                        break;
                }
            }
            unset( $line );

            $handle = fopen( $directory . 'wp-config.php', 'w' );
            foreach ( $config_file as $line ) {
                fwrite( $handle, $line );
            }
            fclose( $handle );

            // We set the good rights to the wp-config file
            chmod( $directory . 'wp-config.php', 0666 );
            unlink( $directory . '/wp-config-sample.php' ); // We remove wp-config-sample

            break;

        case 'install_wp':
            // Let's install WordPress database

            define( 'WP_INSTALLING', true );

            /** Load WordPress Bootstrap */
            require_once $directory . 'wp-load.php';

            /** Load WordPress Administration Upgrade API */
            require_once $directory . 'wp-admin/includes/upgrade.php';

            /** Load wpdb */
            require_once $directory . 'wp-includes/wp-db.php';

            // WordPress installation
            wp_install( $_POST['weblog_title'], $_POST['user_login'], $_POST['admin_email'], true, '', $_POST['admin_password'] );

            // We update the options with the right siteurl et homeurl value
            $protocol = !is_ssl() ? 'http' : 'https';
            $get      = basename( dirname( __FILE__ ) ) . '/index.php/wp-admin/install.php?action=install_wp';
            $dir      = str_replace( '../', '', $directory );
            $link     = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url      = str_replace( $get, $dir, $link );
            $url      = trim( $url, '/' );

            update_option( 'siteurl', $url );
            update_option( 'home', $url );

            // We remove the default content

            wp_delete_post( 1, true ); // We remove the article "Hello World"
            wp_delete_post( 2, true ); // We remove the "Exemple page"

            // We update permalinks
            if ( !empty( $_POST['permalink_structure'] ) ) {
                update_option( 'permalink_structure', $_POST['permalink_structure'] );
            }

            update_option( 'uploads_use_yearmonth_folders', 0 );

            // We add the pages we found in the data.ini file

            // We check if data.ini exists
            if ( file_exists( 'data.ini' ) ) {

                // We parse the file and get the array
                $file  = parse_ini_file( 'data.ini' );
                $posts = $file['posts'] ?? array();

                // We verify if we have at least one page
                if ( count( $posts ) >= 1 ) {

                    foreach ( $posts as $post ) {

                        // We get the line of the page configuration
                        $pre_config_post = explode( '-', $post );
                        $post            = array();

                        foreach ( $pre_config_post as $config_post ) {

                            // We retrieve the page title
                            if ( preg_match( '#title::#', $config_post ) == 1 ) {
                                $post['title'] = str_replace( 'title::', '', $config_post );
                            }

                            // We retrieve the status (publish, draft, etc...)
                            if ( preg_match( '#status::#', $config_post ) == 1 ) {
                                $post['status'] = str_replace( 'status::', '', $config_post );
                            }

                            // On retrieve the post type (post, page or custom post types ...)
                            if ( preg_match( '#type::#', $config_post ) == 1 ) {
                                $post['type'] = str_replace( 'type::', '', $config_post );
                            }

                            // We retrieve the content
                            if ( preg_match( '#content::#', $config_post ) == 1 ) {
                                $post['content'] = str_replace( 'content::', '', $config_post );
                            }

                            // We retrieve the slug
                            if ( preg_match( '#slug::#', $config_post ) == 1 ) {
                                $post['slug'] = str_replace( 'slug::', '', $config_post );
                            }

                            // We retrieve the title of the parent
                            if ( preg_match( '#parent::#', $config_post ) == 1 ) {
                                $post['parent'] = str_replace( 'parent::', '', $config_post );
                            }
                        } // foreach

                        if ( isset( $post['title'] ) && !empty( $post['title'] ) ) {

                            $parent = get_page_by_title( trim( $post['parent'] ) );
                            $parent = $parent ? $parent->ID : 0;

                            // Let's create the page
                            $args = array(
                                'post_title'     => trim( $post['title'] ),
                                'post_name'      => $post['slug'],
                                'post_content'   => trim( $post['content'] ),
                                'post_status'    => $post['status'],
                                'post_type'      => $post['type'],
                                'post_parent'    => $parent,
                                'post_author'    => 1,
                                'post_date'      => date( 'Y-m-d H:i:s' ),
                                'post_date_gmt'  => gmdate( 'Y-m-d H:i:s' ),
                                'comment_status' => 'closed',
                                'ping_status'    => 'closed',
                            );
                            wp_insert_post( $args );

                        }
                    }
                }
            }

            // Create Accueil page
            $post_accueil = wp_insert_post(
                array(
                    'post_title'   => 'Accueil',
                    'post_name'    => 'accueil',
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1,
                )
            );

            if ( $post_accueil && !is_wp_error( $post_accueil ) ) {
                update_post_meta( $post_accueil, '_wp_page_template', 'templates/accueil.php' );
            }

            // Create Blog Page
            $post_blog = wp_insert_post(
                array(
                    'post_title'   => 'Blog',
                    'post_name'    => 'blog',
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1,
                )
            );

            // Create Contact Page
            $post_contact = wp_insert_post(
                array(
                    'post_title'   => 'Contact',
                    'post_name'    => 'contact',
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1,
                )
            );

            // Create Cookies Page
            $post_cookies = wp_insert_post(
                array(
                    'post_title'   => 'Cookies',
                    'post_name'    => 'cookies',
                    'post_content' => '[cmplz-document type="cookie-statement" region="eu"][cmplz-cookies]',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1,
                )
            );

            // Create Mentions légales Page
            $post_mentions_legales = wp_insert_post(
                array(
                    'post_title'   => 'Mentions légales',
                    'post_name'    => 'mentions-legales',
                    'post_content' => '[cmplz-document type="disclaimer" region="all"]',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1,
                )
            );

            // Create Menu & Add Demo Page
            $menu_id = wp_create_nav_menu( 'Menu principal' );

            /** Blog Link */
            wp_update_nav_menu_item(
                $menu_id,
                0,
                array(
                    'menu-item-title'     => get_page_by_path( 'blog' )->post_title,
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => get_page_by_path( 'blog' )->ID,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                )
            );

            /** Contact Link */
            wp_update_nav_menu_item(
                $menu_id,
                0,
                array(
                    'menu-item-title'     => get_page_by_path( 'contact' )->post_title,
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => get_page_by_path( 'contact' )->ID,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                )
            );

            // WP Settings: Front Page = Accueil
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $post_accueil );

            // WP Settings: Blog Page = Blog
            update_option( 'page_for_posts', $post_blog );

            // WP Settings: Disable comments globally
            update_option( 'default_comment_status', 'closed' );
            update_option( 'comments_notify', 0 );
            update_option( 'comment_moderation', 1 );
            update_option( 'comment_registration', 1 );
            update_option( 'page_comments', 0 );

            break;

        case 'install_theme':
            /** Load WordPress Bootstrap */
            require_once $directory . 'wp-load.php';

            /** Load WordPress Administration Upgrade API */
            require_once $directory . 'wp-admin/includes/upgrade.php';

            // Let's remove the Twenty family
            delete_theme( 'twentytwentytwo' );
            delete_theme( 'twentytwentyone' );
            delete_theme( 'twentytwenty' );
            delete_theme( 'twentynineteen' );
            delete_theme( 'twentyeighteen' );
            delete_theme( 'twentyseventeen' );
            delete_theme( 'twentysixteen' );
            delete_theme( 'twentyfifteen' );
            delete_theme( 'twentyfourteen' );
            delete_theme( 'twentythirteen' );
            delete_theme( 'twentytwelve' );
            delete_theme( 'twentyeleven' );
            delete_theme( 'twentyten' );

            // We delete the _MACOSX folder (bug with a Mac)
            delete_theme( '__MACOSX' );

            // Download theme from our git repo
            $theme_path = 'theme.zip';
            if ( !file_exists( $theme_path ) ) {

                $theme_url = 'https://github.com/Pilot-in/PiloPress-Private-Starter-Theme/archive/main.zip';
                $token     = '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';
                $curl      = curl_init();
                curl_setopt_array(
                    $curl,
                    [
                        CURLOPT_URL            => $theme_url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST  => 'GET',
                        CURLOPT_HTTPHEADER     => [
                            'Authorization: token ' . $token,
                            'Content-Type: application/x-www-form-urlencoded',
                            'User-Agent: PHP',
                        ],
                    ]
                );
                $response = curl_exec( $curl );
                $error    = curl_error( $curl );
                curl_close( $curl );
                if ( $error ) {
                    echo 'cURL Error #:' . $error;
                } else {
                    $theme_url = explode( 'href="', $response );
                    $theme_url = explode( '">', $theme_url['1'] );
                    $theme_url = $theme_url[0];
                }

                if ( $theme_file = file_get_contents( $theme_url ) ) {
                    file_put_contents( $theme_path, $theme_file );
                }
            }

            // We verify if theme.zip exists
            if ( file_exists( $theme_path ) ) {

                $zip = new ZipArchive();

                // We verify we can use it
                if ( $zip->open( $theme_path ) === true ) {

                    // We retrieve the name of the folder
                    $stat       = $zip->statIndex( 0 );
                    $theme_name = str_replace( '/', '', $stat['name'] );

                    // We unzip the archive in the themes folder
                    $theme_dir = $directory . 'wp-content/themes/';
                    $zip->extractTo( $theme_dir );
                    $zip->close();

                    // Replace downloaded folder name by current project name
                    $project_name   = isset( $_POST['weblog_title'] ) ? $_POST['weblog_title'] : '';
                    $new_theme_name = $theme_name;
                    if ( $project_name ) {
                        $new_theme_name = sanitize_title( $project_name );
                        rename( $theme_dir . $theme_name, $theme_dir . $new_theme_name );
                    }

                    // Delete remaining dotfiles
                    $files_type_found = find_dot_files( $theme_dir . $new_theme_name );
                    if ( !empty( $files_type_found ) ) {

                        foreach ( $files_type_found as $files_found ) {
                            if ( !empty( $files_found ) ) {
                                foreach ( $files_found as $file_found ) {
                                    unlink( $file_found );
                                }
                            }
                        }
                    }

                    // Let's activate the theme
                    // Note : The theme is automatically activated if the user asked to remove the default theme
                    switch_theme( $new_theme_name, $new_theme_name );

                }
            }

            break;

        case 'install_plugins':
            // Let's retrieve the plugin folder

            if ( !empty( $_POST['plugins'] ) ) {

                $plugins     = explode( ';', $_POST['plugins'] );
                $plugins     = array_map( 'trim', $plugins );
                $plugins_dir = $directory . 'wp-content/plugins/';

                foreach ( $plugins as $plugin ) {

                    // We retrieve the plugin XML file to get the link to download it
                    $plugin_repo = file_get_contents( "http://api.wordpress.org/plugins/info/1.0/$plugin.json" );
                    $plugin_obj = json_decode( $plugin_repo );

                    // If the plugin is on the repo
                    if ( $plugin_repo && $plugin_obj && isset( $plugin_obj->slug ) ) {

                        $plugin_path = WPQI_CACHE_PLUGINS_PATH . $plugin_obj->slug . '-' . $plugin_obj->version . '.zip';

                        if ( !file_exists( $plugin_path ) ) {
                            // We download the lastest version
                            if ( $download_link = file_get_contents( $plugin_obj->download_link ) ) {
                                file_put_contents( $plugin_path, $download_link );
                            }
                        }

                        if ( file_exists( $plugin_path ) ) {

                            // We unzip it
                            $zip = new ZipArchive();
                            if ( $zip->open( $plugin_path ) === true ) {
                                $zip->extractTo( $plugins_dir );
                                $zip->close();
                            }
                        }

                    // If the plugin is not on the repo but we hade the zip archive
                    } else {

                        $plugin_path = WPQI_CACHE_PLUGINS_PATH . $plugin . '.zip';
                        if ( file_exists( $plugin_path ) ) {

                            // We unzip it
                            $zip = new ZipArchive();
                            if ( $zip->open( $plugin_path ) === true ) {
                                $zip->extractTo( $plugins_dir );
                                $zip->close();
                            }

                        } else {

                            error_log( "Plugin: '$plugin' not found." );

                        }

                    }

                }
            }

            if ( !empty( $_POST['premium_plugins'] ) ) {

                $premium_plugins     = explode( ';', $_POST['premium_plugins'] );
                $premium_plugins     = array_map( 'trim', $premium_plugins );

                // We scan the folder
                // $plugins = scandir( 'plugins' );

                // We remove the "." and ".." corresponding to the current and parent folder
                // $premium_plugins = array_diff( $premium_plugins, array( '.', '..' ) );

                // We move the archives and we unzip
                foreach ( $premium_plugins as $plugin_slug ) {

                    // $plugin_slug = substr( $plugin, 0, -4 );
                    $plugin_path = WPQI_PREMIUM_PLUGINS_PATH . $plugin_slug . '.zip';

                    // We verify if we have to retrive somes plugins via the PiloPress Installer "plugins" folder
                    // if ( file_exists( $plugin_path ) ) {

                        // error_log( "Plugin_slug: $plugin_slug" );
                        // error_log( "Plugin_path: $plugin_path" );

                        /**
                         *  ACF Pro
                         *  - Download process
                         */
                        if ( $plugin_slug === 'advanced-custom-fields-pro' ) {

                            // $plugin_slug = 'advanced-custom-fields-pro';
                            // $plugin_path = WPQI_PREMIUM_PLUGINS_PATH . $plugin_slug . '.zip';
                            $licence     = $_REQUEST['acf_licence'] ?? '';

                            /** Force "download" if licence is given (for latest version) */
                            if ( $licence ) {

                                $plugin_url = 'https://connect.advancedcustomfields.com/index.php?p=pro&a=download&k=' . $licence;
                                if ( $plugin_file = file_get_contents( $plugin_url ) ) {

                                    // Delete old zip first
                                    if ( file_exists( $plugin_path ) ) {
                                        @unlink( $plugin_path );
                                    }

                                    file_put_contents( $plugin_path, $plugin_file );
                                }
                            }

                        }

                        /**
                        *  ACF Extended Pro
                        *  - Download process
                        */
                        elseif ( $plugin_slug === 'acf-extended-pro' ) {

                            // $plugin_slug = 'acf-extended-pro';
                            // $plugin_path = WPQI_PREMIUM_PLUGINS_PATH . $plugin_slug . '.zip';
                            $licence     = $_REQUEST['acfe_licence'] ?? '';

                            // TODO: Update to use future working download url (with licence key) when it will be available
                            // $plugin_url = 'https://www.acf-extended.com/index.php?eddfile=1279%3A949%3A1%3A3&ttl=1607016426&file=1&token=42c5f5f5c5d0f4e7ec72d28c4c57487cba2b62ed69f4de36c9446f65c85d3468';
                            // if ( $plugin_file = file_get_contents( $plugin_url ) ) {

                            //     // Delete old zip first
                            //     if ( file_exists( $plugin_path ) ) {
                            //         @unlink( $plugin_path );
                            //     }

                            //     file_put_contents( $plugin_path, $plugin_file );
                            // }

                        }

                        /**
                        *  Admin Columns Pro
                        *  - Download process
                        */
                        elseif ( $plugin_slug === 'admin-columns-pro' ) {

                            // $plugin_slug = 'admin-columns-pro';
                            // $plugin_path = WPQI_PREMIUM_PLUGINS_PATH . $plugin_slug . '.zip';
                            $licence     = $_REQUEST['acp_licence'] ?? '';

                            /** Force "download" if licence is given (for latest version) */
                            if ( $licence ) {

                                $plugin_url = 'https://www.admincolumns.com/?command=download&subscription_key=' . $licence . '&product_key=' . $plugin_slug;
                                if ( $plugin_file = file_get_contents( $plugin_url ) ) {

                                    // Delete old zip first
                                    if ( file_exists( $plugin_path ) ) {
                                        @unlink( $plugin_path );
                                    }

                                    file_put_contents( $plugin_path, $plugin_file );
                                }
                            }

                        }

                        /**
                        *  Admin Columns Pro ACF addon
                        *  - Download process
                        */
                        elseif ( $plugin_slug === 'ac-addon-acf' ) {

                            // $plugin_slug = 'ac-addon-acf';
                            // $plugin_path = WPQI_PREMIUM_PLUGINS_PATH . $plugin_slug . '.zip';
                            $licence     = $_REQUEST['acp_licence'] ?? '';

                            /** Force "download" if licence is given (for latest version) */
                            if ( $licence ) {

                                $plugin_url = 'https://www.admincolumns.com/?command=download&subscription_key=' . $licence . '&product_key=' . $plugin_slug;
                                if ( $plugin_file = file_get_contents( $plugin_url ) ) {

                                    // Delete old zip first
                                    if ( file_exists( $plugin_path ) ) {
                                        @unlink( $plugin_path );
                                    }

                                    file_put_contents( $plugin_path, $plugin_file );
                                }
                            }

                        }

                        /**
                        *  Admin Columns Pro WooCommerce addon
                        *  - Download process
                        */
                        elseif ( $plugin_slug === 'ac-addon-woocommerce' ) {

                            // $plugin_slug = 'ac-addon-woocommerce';
                            // $plugin_path = WPQI_PREMIUM_PLUGINS_PATH . $plugin_slug . '.zip';
                            $licence     = $_REQUEST['acp_licence'] ?? '';

                            /** Force "download" if licence is given (for latest version) */
                            if ( $licence ) {

                                $plugin_url = 'https://www.admincolumns.com/?command=download&subscription_key=' . $licence . '&product_key=' . $plugin_slug;
                                if ( $plugin_file = file_get_contents( $plugin_url ) ) {

                                    // Delete old zip first
                                    if ( file_exists( $plugin_path ) ) {
                                        @unlink( $plugin_path );
                                    }

                                    file_put_contents( $plugin_path, $plugin_file );
                                }
                            }

                        }

                        /**
                         *  Pilopress Addon
                         *  - Download process
                         */
                        elseif ( $plugin_slug === 'pilopress-addon' ) {

                            // Download plugin from our git repo
                            if ( !file_exists( $plugin_path ) ) {

                                $plugin_url = 'https://github.com/Pilot-in/PiloPress-Addon/archive/master.zip';
                                $token     = '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';
                                $curl      = curl_init();
                                curl_setopt_array(
                                    $curl,
                                    [
                                        CURLOPT_URL            => $plugin_url,
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_CUSTOMREQUEST  => 'GET',
                                        CURLOPT_HTTPHEADER     => [
                                            "Authorization: token $token",
                                            'Content-Type: application/x-www-form-urlencoded',
                                            'User-Agent: PHP',
                                        ],
                                    ]
                                );
                                $response = curl_exec( $curl );
                                $error    = curl_error( $curl );
                                curl_close( $curl );
                                if ( $error ) {
                                    echo 'cURL Error #:' . $error;
                                } else {
                                    $plugin_url = explode( 'href="', $response );
                                    $plugin_url = explode( '">', $plugin_url['1'] );
                                    $plugin_url = $plugin_url[0];
                                }

                                if ( $plugin_file = file_get_contents( $plugin_url ) ) {
                                    file_put_contents( $plugin_path, $plugin_file );
                                }
                            }

                            if ( file_exists( $plugin_path ) ) {

                                // We unzip it
                                $zip = new ZipArchive();
                                if ( $zip->open( $plugin_path ) === true ) {

                                    $zip->extractTo( $plugins_dir );
                                    $zip->close();

                                    // Delete remaining dotfiles
                                    $files_type_found = find_dot_files( $plugins_dir . $plugin_slug );
                                    if ( !empty( $files_type_found ) ) {

                                        foreach ( $files_type_found as $files_found ) {
                                            if ( !empty( $files_found ) ) {
                                                foreach ( $files_found as $file_found ) {
                                                    unlink( $file_found );
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            continue;
                        }

                        /**
                         *  WP Rocket
                         *  - Download process
                         */
                        elseif ( $plugin_slug === 'wp-rocket' ) {

                            // $plugin_slug = 'wp-rocket';
                            // $plugin_path = WPQI_CACHE_PLUGINS_PATH . $plugin_slug . '.zip';
                            $plugin_url = 'https://wp-rocket.me/download/17718/81de709f/';
                            if ( $plugin_file = file_get_contents( $plugin_url ) ) {

                                // Delete old zip first
                                if ( file_exists( $plugin_path ) ) {
                                    @unlink( $plugin_path );
                                }

                                file_put_contents( $plugin_path, $plugin_file );
                            }

                        }

                        $zip = new ZipArchive;

                        // We verify we can use the archive
                        if ( $zip->open( $plugin_path ) === true ) {

                            // We unzip the archive in the plugin folder
                            $zip->extractTo( $plugins_dir );
                            $zip->close();

                        }
                    // }
                }
            }

            // We activate extensions

            if ( !empty( $_POST['activate_plugins'] ) ) {

                /** Load WordPress Bootstrap */
                require_once $directory . 'wp-load.php';

                /** Load WordPress Plugin API */
                require_once $directory . 'wp-admin/includes/plugin.php';

                // Get selected plugins
                $activate_plugins_slug = array();
                $activate_plugins_str  = $_POST['activate_plugins'] ?? array();
                if ( $activate_plugins_str ) {

                    $activate_plugins_slug = explode( ';', $activate_plugins_str );
                    if ( empty( $activate_plugins_slug ) ) {
                        $activate_plugins_slug = array( $activate_plugins_str );
                    }
                    $activate_plugins_slug = array_map( 'trim', $activate_plugins_slug );

                }

                if ( !empty( $activate_plugins_slug ) ) {

                    $installed_plugins = array_keys( get_plugins() );

                    foreach ( $activate_plugins_slug as $activate_plugin_slug ) {
                        foreach ( $installed_plugins as $installed_plugin ) {
                            if ( mb_stripos( $installed_plugin, $activate_plugin_slug ) !== false ) {
                                activate_plugin( $installed_plugin );
                            }
                        }
                    }
                }
            }


            /**
             *  WooCommerce - Setup Config
             */
            if ( class_exists( 'WooCommerce' ) ) {

                /**
                 *  Create "Boutique" page
                 *  (WooCommerce only)
                 */
                $post_shop = wp_insert_post(
                    array(
                        'post_title'   => 'Boutique',
                        'post_name'    => 'boutique',
                        'post_content' => '',
                        'post_status'  => 'publish',
                        'post_type'    => 'page',
                        'post_author'  => 1,
                    )
                );

                if ( $post_shop && !is_wp_error( $post_shop ) ) {
                    update_option( 'woocommerce_shop_page_id', $post_shop );
                }

                /**
                 *  Create "Panier" page
                 *  (WooCommerce only)
                 */
                $post_cart = wp_insert_post(
                    array(
                        'post_title'   => 'Panier',
                        'post_name'    => 'panier',
                        'post_content' => '',
                        'post_status'  => 'publish',
                        'post_type'    => 'page',
                        'post_author'  => 1,
                    )
                );

                if ( $post_cart && !is_wp_error( $post_cart ) ) {
                    update_option( 'woocommerce_cart_page_id', $post_cart );
                }

                /**
                 *  Create "Commande" page
                 *  (WooCommerce only)
                 */
                $post_checkout = wp_insert_post(
                    array(
                        'post_title'   => 'Validation de la commande',
                        'post_name'    => 'commande',
                        'post_content' => '',
                        'post_status'  => 'publish',
                        'post_type'    => 'page',
                        'post_author'  => 1,
                    )
                );

                if ( $post_checkout && !is_wp_error( $post_checkout ) ) {
                    update_option( 'woocommerce_checkout_page_id', $post_checkout );
                }

                /**
                 *  Create "Mon compte" page
                 *  (WooCommerce only)
                 */
                $post_myaccount = wp_insert_post(
                    array(
                        'post_title'   => 'Mon compte',
                        'post_name'    => 'mon-compte',
                        'post_content' => '',
                        'post_status'  => 'publish',
                        'post_type'    => 'page',
                        'post_author'  => 1,
                    )
                );

                if ( $post_myaccount && !is_wp_error( $post_myaccount ) ) {
                    update_option( 'woocommerce_myaccount_page_id', $post_myaccount );
                }


                /**
                 *  Get Main menu
                 */
                $main_menu = wp_get_nav_menu_object( 'Menu' );
                $menu_id   = $main_menu->term_id;

                /**
                 *  Shop Link
                 *  (WooCommerce only)
                 */
                wp_update_nav_menu_item(
                    $menu_id,
                    0,
                    array(
                        'menu-item-title'     => get_page_by_path( 'boutique' )->post_title,
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $post_shop,
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                    )
                );

                /**
                 *  My Account Link
                 *  (WooCommerce only)
                 */
                wp_update_nav_menu_item(
                    $menu_id,
                    0,
                    array(
                        'menu-item-title'     => get_page_by_path( 'mon-compte' )->post_title,
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $post_myaccount,
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                    )
                );
            }

            /**
             *  Yoast - Setup Config
             */
            if ( defined( 'WPSEO_VERSION' ) ) {

                $yoast_option_titles = get_option( 'wpseo_titles' );
                if ( is_array( $yoast_option_titles ) && !empty( $yoast_option_titles ) ) {

                    /** Enable breadcrumb */
                    $yoast_option_titles['breadcrumbs-enable'] = 'on';

                    /** Breadcrumb "home" text */
                    $yoast_option_titles['breadcrumbs-home'] = 'Accueil';

                    /** Breadcrumb symbol separator */
                    $yoast_option_titles['breadcrumbs-sep'] = '>';

                    /** Update new YOAST SEO options */
                    update_option( 'wpseo_titles', $yoast_option_titles );

                }
            }

            break;

        case 'success':
            // If we have a success we add the link to the admin and the website

            /** Load WordPress Bootstrap */
            require_once $directory . 'wp-load.php';

            /** Load WordPress Administration Upgrade API */
            require_once $directory . 'wp-admin/includes/upgrade.php';


            /**
             *  We update licences keys if there are set
             */

            // ACF PRO
            $acf_licence = $_REQUEST['acf_licence'] ?? '';
            if ( $acf_licence && function_exists( 'acf_pro_update_license' ) ) {
                acf_pro_update_license( $acf_licence );
            }

            // Admin Columns Pro
            $acp_licence = $_REQUEST['acp_licence'] ?? '';
            if ( $acp_licence ) {
                update_option( 'acp_subscription_key', $acp_licence, 'no' );
                update_option( 'acp_subscription_details_key', $acp_licence, 'no' );
            }

            // ACF Extended Pro
            $acfe_licence = $_REQUEST['acfe_licence'] ?? '';
            if ( $acfe_licence && function_exists( 'acfe_settings' ) ) {
                acfe_settings( 'license', $acfe_licence, true );
            }

            // Imagify
            $imagify_licence = $_REQUEST['imagify_licence'] ?? '';
            $old_option = get_option('imagify_settings');
            $old_option['api_key'] = $imagify_licence;
            update_option( 'imagify_settings', $old_option, true);


            // We update permalinks
            if ( !empty( $_POST['permalink_structure'] ) ) {
                file_put_contents( $directory . '.htaccess', null );
                flush_rewrite_rules();
            }

            if ( isset( $_POST['remove_install'] ) && $_POST['remove_install'] == 1 && is_dir( $_SERVER['DOCUMENT_ROOT'] . '/wp-install' ) ) {
                rrmdir( $_SERVER['DOCUMENT_ROOT'] . '/wp-install' );
            } else {
                echo '<div id="errors" class="alert alert-danger"><p style="margin:0;"><strong>' . _( 'Warning' ) . '</strong>: Don\'t forget to delete PiloPress Installer folder.</p></div>';
            }

            // Link to the admin
            ob_start(); ?>
            <a href="<?php echo wp_login_url(); ?>" class="button" style="margin-right:5px;" target="_blank"><?php _e( 'Log In' ); ?></a>
            <a href="<?php echo home_url(); ?>" class="button" target="_blank"><?php _e( 'Go to website' ); ?></a>
            <?php
            echo ob_get_clean();

            break;
    }
} else { ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <meta charset="utf-8"/>
        <title>PiloPress Installer</title>
        <!-- Get out Google! -->
        <meta name="robots" content="noindex, nofollow">
        <link rel="icon"
              type="image/svg"
              href="assets/images/favicon.svg">
        <!-- CSS files -->
        <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=3.9.1"/>
        <link rel="stylesheet" href="assets/css/style.min.css"/>
        <link rel="stylesheet" href="assets/css/buttons.min.css"/>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
    </head>
    <body class="wp-core-ui">
    <h1 id="logo"><a href="https://pilot-in.com">Pilot'In</a></h1>
    <?php
    $parent_dir = realpath( dirname( dirname( __FILE__ ) ) );
    if ( is_writable( $parent_dir ) ) { ?>

        <div id="response"></div>
        <div class="progress" style="display:none;">
            <div class="progress-bar progress-bar-striped active" style="width: 0%;"></div>
        </div>
        <div id="success" style="display:none; margin: 10px 0;">
            <h1 style="margin: 0"><?php echo _( 'The world is yours' ); ?></h1>
            <p><?php echo _( 'WordPress has been installed.' ); ?></p>
        </div>
        <form method="post" action="">

            <div id="errors" class="alert alert-danger" style="display:none;">
                <strong><?php echo _( 'Warning' ); ?></strong>
            </div>

            <h1><?php echo _( 'Database' ); ?></h1>

            <table class="form-table">

                <?php
                // Prefill DB Name based on "http host" (laragon)
                $domain = $_SERVER['HTTP_HOST'] ?? '';
                $domain = $domain ? explode('.', $domain) : '';
                $domain = is_array( $domain ) ? $domain[0] : '';
                ?>
                <tr>
                    <th scope="row"><label for="dbhost"><?php echo _( 'Database Host' ); ?></label></th>
                    <td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" class="required"/></td>
                    <td><?php echo _( 'You should be able to get this info from your web host, if <code>localhost</code> does not work.' ); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="dbport"><?php echo _( 'Database port' ); ?></label></th>
                    <td><input name="dbport" id="dbport" type="text" value="3306" size="25" class="required"/></td>
                    <td><?php echo _( 'Database port' ); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="dbname"><?php echo _( 'Database name' ); ?></label></th>
                    <td><input name="dbname" id="dbname" type="text" size="25" value="<?php echo $domain; ?>"
                               class="required"/></td>
                    <td><?php echo _( 'The name of the database you want to run WP in.' ); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="uname"><?php echo _( 'Database username' ); ?></label></th>
                    <td><input name="uname" id="uname" type="text" size="25" value="<?php echo ( isset( $_POST['domaine'] ) ) ? 'pilotin' : 'root'; ?>" class="required"/></td>
                    <td><?php echo _( 'Your MySQL username' ); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pwd"><?php echo _( 'Password' ); ?></label></th>
                    <td><input name="pwd" id="pwd" type="text" size="25" value="<?php echo ( isset( $_POST['domaine'] ) ) ? 'fNXdAVi7srSZ' : ''; ?>"/></td>
                    <td><?php echo _( '&hellip;and your MySQL password.' ); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prefix"><?php echo _( 'Table Prefix' ); ?></label></th>
                    <td><input name="prefix" id="prefix" type="text" value="wp_" size="25" class="required"/></td>
                    <td><?php echo _( 'If you want to run multiple WordPress installations in a single database, change this.' ); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_content"><?php echo _( 'Default content' ); ?></label></th>
                    <td>
                        <label><input type="checkbox" name="default_content" id="default_content" value="1" checked="checked"/> <?php echo _( 'Delete the content' ); ?></label>
                    </td>
                    <td><?php echo _( 'If you want to delete the default content added par WordPress (post, page, comment and links).' ); ?></td>
                </tr>
            </table>

            <h1><?php echo _( 'Informations' ); ?></h1>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="language"><?php echo _( 'Language' ); ?></label></th>
                    <td>
                        <select id="language" name="language">
                            <option value="en_US">English (United States)</option>
                            <?php
                            // Get all available languages
                            $languages = json_decode( file_get_contents( 'http://api.wordpress.org/translations/core/1.0/?version=4.0' ) )->translations;

                            foreach ( $languages as $language ) {
                                echo '<option value="' . $language->language . '">' . $language->native_name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="directory"><?php echo _( 'Installation Folder' ); ?></label>
                        <p><?php echo _( 'Leave blank to install on the root folder' ); ?></p>
                    </th>
                    <td>
                        <input name="directory" type="text" id="directory" size="25" value=""/>
                    </td>
                </tr>

                <?php
                // Project name based on http host
                $project_name = $domain ? ucfirst( $domain ) : 'Project name'; ?>
                <tr>
                    <th scope="row"><label for="weblog_title"><?php echo _( 'Site Title' ); ?></label></th>
                    <td><input name="weblog_title" type="text" id="weblog_title" size="25" value="<?php echo $project_name; ?>"
                               class="required"/></td>
                </tr>
                <tr>
                    <th scope="row"><label for="user_login"><?php echo _( 'Username' ); ?></label></th>
                    <td>
                        <input name="user_login" type="text" id="user_login" size="25" value="" class="required"/>
                        <p><?php echo _( 'Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods and the @ symbol.' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="admin_password"><?php echo _( 'Password' ); ?></label>
                        <p><?php echo _( 'A password will be automatically generated for you if you leave this blank.' ); ?></p>
                    </th>
                    <td>
                        <input name="admin_password" type="password" id="admin_password" size="25" value=""/>
                        <p><?php echo _( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).' ); ?>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_email"><?php echo _( 'Your E-mail' ); ?></label></th>
                    <td><input name="admin_email" type="text" id="admin_email" size="25" value="" class="required"/>
                        <p><?php echo _( 'Double-check your email address before continuing.' ); ?></p></td>
                </tr>
            </table>

            <h1><?php echo _( 'Extensions' ); ?></h1>
            <p><?php echo _( 'Simply enter below the extensions that should be added during the installation.' ); ?></p>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="acf_licence"><?php echo _( 'ACF Licence Key' ); ?></label>
                    </th>
                    <td>
                        <input id="acf_licence" name="acf_licence" type="text" value="b3JkZXJfaWQ9NzM5MjZ8dHlwZT1wZXJzb25hbHxkYXRlPTIwMTYtMDEtMzEgMTA6NTk6MDM=" readonly required/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="acp_licence"><?php echo _( 'Admin Columns Pro Licence Key' ); ?></label>
                    </th>
                    <td>
                        <input id="acp_licence" name="acp_licence" type="text" value="f7c01569-d2d7-41b1-bee4-531d2e827be4" readonly required/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="acfe_licence"><?php echo _( 'ACF Extended Licence Key' ); ?></label>
                    </th>
                    <td>
                        <input id="acfe_licence" name="acfe_licence" type="text" value="a2V5PTE4OTBmMDJmODQxOTUwZjY4NzM5MWRjNmNlNzZjZmZl" readonly required/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="imagify_licence"><?php echo _( 'Imagify Licence Key' ); ?></label>
                    </th>
                    <td>
                        <input id="imagify_licence" name="imagify_licence" type="text" value="e454453879b94c5100cc9887462da7c626836e57" readonly required/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="plugins"><?php echo _( 'Extensions activation' ); ?></label>
                        <p><?php echo _( 'Activate the extensions after WordPress installation.' ); ?></p>
                    </th>
                    <td class="activate_plugins">
                        <label>
                            <input name="plugins" type="hidden" id="plugins" size="50" value=""/>
                            <input name="premium_plugins" type="hidden" id="premium_plugins" value=""/>
                        </label>
                    </td>
                </tr>
            </table>

            <h1><?php echo _( 'Permalinks' ); ?></h1>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="permalink_structure"><?php echo _( 'Custom Structure' ); ?></label>
                    </th>
                    <td>
                        <code>http://<?php echo $_SERVER['SERVER_NAME']; ?></code>
                        <input name="permalink_structure" type="text" id="permalink_structure" size="50" value="/%postname%/"/>
                    </td>
                </tr>
            </table>

            <h1><?php echo _( 'wp-config.php' ); ?></h1>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="debug">Debug</label>
                    </th>
                    <td>
                        <label><input type="checkbox" name="debug" id="debug" value="1" checked/> WP_Debug</label><br/>
                        <label><input type="checkbox" name="debug_display" id="debug_display" value="1" checked/> WP_Debug_Display</label><br/>
                        <label><input type="checkbox" name="debug_log" id="debug_log" value="1" checked/> WP_Debug_Log</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="debug">WP Install</label>
                    </th>
                    <td>
                        <label><input type="checkbox" name="remove_install" id="remove_install" value="1" checked/> Delete <code>wp-install</code> folder after install.
                    </td>
                </tr>
            </table>
            <p class="step"><span id="submit" class="button button-large"><?php echo _( 'Install WordPress' ); ?></span></p>

        </form>

        <script src="assets/js/jquery-1.8.3.min.js"></script>
        <script>var data = <?php echo $data; ?>;</script>
        <script src="assets/js/script.js"></script>
    <?php
    } else { ?>

        <div class="alert alert-error" style="margin-bottom: 0px;">
            <strong><?php echo _( 'Warning !' ); ?></strong>
            <p style="margin-bottom:0px;"><?php echo _( 'You don\'t have the good permissions rights on ' ) . basename( $parent_dir ) . _( '. Thank you to set the good files permissions.' ); ?></p>
        </div>

        <?php
    }
    ?>
    </body>
    </html>
    <?php
}

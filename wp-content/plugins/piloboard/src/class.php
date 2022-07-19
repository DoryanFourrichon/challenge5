<?php

if ( !class_exists( 'PiloBoard' ) ) {
    /**
     * Init Class PiloBoard
     */
    class PiloBoard {

        /**
         * Constructor
         */
        public function __construct() {
            if ( is_multisite() ) :
                add_action( 'network_admin_menu', array( $this, 'register_piloboard_network_admin_menu' ), 500 ); // Add node to Menu
                add_action( 'wp_network_dashboard_setup', array( $this, 'add_widgets_to_dashboard' ) );           // Display widgets
                add_action( 'wp_dashboard_setup', array( $this, 'add_widgets_to_dashboard' ) ); // Display widgets
            else :
                add_action( 'admin_menu', array( $this, 'register_piloboard_menu' ), 500 ); // Add node to Menu
                add_action( 'wp_dashboard_setup', array( $this, 'add_widgets_to_dashboard' ) ); // Display widgets
            endif;

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_custom_styles' ) );            // Enqueue Back-End
            add_action( 'wp_ajax_send_licence', array( $this, 'send_licence' ), 0 );                   // Send Licence
            add_action( 'upgrader_process_complete', array( $this, 'delete_old_transients' ), 10, 2 ); // Remove old version transient after update
        }

        /**
         * Remove old transients
         */
        public function delete_old_transients( $upgrader_object, $options ) {
            $current_plugin = plugin_basename( __FILE__ );
            if ( $options['action'] === 'update' && $options['type'] === 'plugin' ) :
                foreach ( $options['plugins'] as $each_plugin ) :
                    if ( $each_plugin === $current_plugin ) :
                        delete_transient( 'piloboard-faq' );
                        delete_transient( 'piloboard-forfait' );
                        delete_transient( 'piloboard-interlocuteurs' );
                    endif;
                endforeach;
            endif;
        }

        /**
         * API Call
         */
        public function api_call( $url ) {
            $username = 'cabin';
            $password = '&v2XpTCThGP3iDFuaOK%d43N';
            $args     = array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
                ),
            );
            $request  = wp_remote_request( $url, $args );
            if ( $request ) :
                $body     = wp_remote_retrieve_body( $request );
                $response = json_decode( $body, true );
            endif;

            return $response;
        }

        /**
         * Register new menu location
         */
        public function register_piloboard_menu() {
            add_submenu_page( 'tools.php', __( 'Pilo\'Board', 'piloboard' ), __( 'Pilo\'Board', 'piloboard' ), 'manage_options', 'piloboard/settings.php', array( $this, 'display_content' ) );
        }

        /**
         * Register new menu location in admin network multisite
         */
        public function register_piloboard_network_admin_menu() {
            if ( current_user_can( 'manage_network' ) ) :
                add_menu_page( __( 'Pilo\'Board', 'piloboard' ), __( 'Pilo\'Board', 'piloboard' ), 'manage_options', 'piloboard/settings.php', array(
                    $this,
                    'display_content',
                ), 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0iI2E3YWFhZCI+PHBhdGggZD0iTTEwIC4yQzQuNi4yLjMgNC42LjMgMTBzNC40IDkuOCA5LjcgOS44YzIuNiAwIDUuMS0xIDYuOS0yLjggMS44LTEuOCAyLjgtNC4zIDIuOC02LjkgMC01LjUtNC4zLTkuOS05LjctOS45em02LjQgMTYuM2MtMS43IDEuNy00IDIuNi02LjQgMi42LTUgMC05LTQuMS05LTkuMVM1IC45IDEwIC45IDE5IDUgMTkgMTBjMCAyLjUtLjkgNC43LTIuNiA2LjV6Ii8+PHBhdGggZD0iTTEwIDUuM2MtMi41IDAtNC42IDIuMS00LjYgNC43di41Yy4yIDEuOCAxLjQgMy4zIDMgMy45LjUuMiAxIC4zIDEuNS4zLjQgMCAuOS0uMSAxLjMtLjIuMSAwIC4xIDAgLjItLjEuMy0uMS41LS4yLjgtLjMgMCAwIC4xIDAgLjEtLjEgMCAwIC4xIDAgLjEtLjFoLjFzLjEgMCAuMS0uMWMwIDAgLjEgMCAuMS0uMS4yLS4yLjUtLjQuNy0uNmwuMy0uM2MuNi0uOCAxLTEuOSAxLTIuOSAwLTIuNS0yLjEtNC42LTQuNy00LjZ6bTMuMSA3LjNjMC0uMSAwLS4xIDAgMC0uNi0uNC0uNy0uOS0uNy0xLjR2LS40LS4xLS4zYzAtLjctLjItMS41LTEuNS0xLjYtLjUgMC0xLjMuMS0yLjMuNC0uMi0uMS0uNCAwLS42LjEtLjYuMi0xLjIuNC0yIC43IDAtMi4yIDEuOC00IDMuOS00IDEuNSAwIDIuOC44IDMuNSAyLjEuNC42LjYgMS4yLjYgMS45IDAgLjktLjMgMS44LS45IDIuNnoiLz48L3N2Zz4=', null );
            endif;
        }

        /**
         * Display Widgets
         */
        public function add_widgets_to_dashboard() {
            $piloboard_licence = get_option( 'piloboard-licence' );
            if ( !$piloboard_licence ) :
                return;
            endif;

            // Commercial widget
            $piloboard_commercial_cap = apply_filters( 'piloboard/commercial/capability', 'manage_options' );
            if ( current_user_can( $piloboard_commercial_cap ) ) {
                wp_add_dashboard_widget( 'piloboard-commercial', '[Pilot\'in] Interlocuteurs', array( $this, 'pilotin_interlocuteurs' ), null, null, 'normal', 'high' );
            }

            // Forfait widget
            $piloboard_forfait_cap = apply_filters( 'piloboard/forfait/capability', 'manage_options' );
            if ( current_user_can( $piloboard_forfait_cap ) ) {
                wp_add_dashboard_widget( 'piloboard-forfait', '[Pilot\'in] Forfait', array( $this, 'pilotin_forfait' ), null, null, 'normal', 'high' );
            }

            // FAQ widget
            $piloboard_faq_cap = apply_filters( 'piloboard/faq/capability', 'edit_posts' );
            if ( current_user_can( $piloboard_faq_cap ) ) {
                wp_add_dashboard_widget( 'piloboard-faq', '[Pilot\'in] F.A.Q', array( $this, 'pilotin_faq' ), null, null, 'normal', 'high' );
            }
        }

        /**
         * Interlocuteurs
         */
        public function pilotin_interlocuteurs() {

            // Option
            $client_id     = get_option( 'piloboard-client-id' );
            $has_transient = get_transient( 'piloboard-interlocuteurs' );

            // Has Transient stop api call
            if ( !$has_transient ) :
                // Api Call
                $url      = 'https://piloboard.pilot-in.net/wp-json/acf/v3/client/' . $client_id . '/';
                $response = $this->api_call( $url );

                // Fields
                if ( !$response ) :
                    return;
                endif;

                $acf_meta = $response['acf'];

                // Avatar & Tel
                $commercial_array     = $acf_meta['commercial'];
                $commercial_id        = $commercial_array['ID'];
                $commercial_firstname = $commercial_array['user_firstname'];
                $commercial_lastname  = $commercial_array['user_lastname'];
                $commercial_email     = $commercial_array['user_email'];
                $url                  = 'https://piloboard.pilot-in.net/wp-json/acf/v3/users/' . $commercial_id;
                $commercial_infos     = $this->api_call( $url );
                $commercial_tel       = $commercial_infos['acf']['num_tel'];
                $commercial_avatar    = $commercial_infos['acf']['image_de_profil']['sizes']['thumbnail'];

                $acf_meta['commercial']['tel']   = $commercial_tel;
                $acf_meta['commercial']['thumb'] = $commercial_avatar;

                // Avatar & Tel
                $cdp_array     = $acf_meta['charge_de_clientele'];
                $cdp_firstname = $cdp_array['user_firstname'];
                $cdp_lastname  = $cdp_array['user_lastname'];
                $cdp_email     = $cdp_array['user_email'];
                $cdp_id        = $cdp_array['ID'];
                $url           = 'https://piloboard.pilot-in.net/wp-json/acf/v3/users/' . $cdp_id;
                $cdp_infos     = $this->api_call( $url );
                $cdp_tel       = $cdp_infos['acf']['num_tel'];
                $cdp_avatar    = $cdp_infos['acf']['image_de_profil']['sizes']['thumbnail'];

                $acf_meta['charge_de_clientele']['tel']   = $cdp_tel;
                $acf_meta['charge_de_clientele']['thumb'] = $cdp_avatar;
                set_transient( 'piloboard-interlocuteurs', $acf_meta, 2 * HOUR_IN_SECONDS );
            else :
                $acf_meta = $has_transient;

                // Commercial
                $commercial_array     = $acf_meta['commercial'];
                $commercial_id        = $commercial_array['ID'];
                $commercial_firstname = $commercial_array['user_firstname'];
                $commercial_lastname  = $commercial_array['user_lastname'];
                $commercial_email     = $commercial_array['user_email'];
                $commercial_avatar    = $commercial_array['thumb'];
                $commercial_tel       = $commercial_array['tel'];

                // Chef de projet
                $cdp_array     = $acf_meta['charge_de_clientele'];
                $cdp_id        = $cdp_array['ID'];
                $cdp_firstname = $cdp_array['user_firstname'];
                $cdp_lastname  = $cdp_array['user_lastname'];
                $cdp_email     = $cdp_array['user_email'];
                $cdp_tel       = $cdp_array['tel'];
                $cdp_avatar    = $cdp_array['thumb'];

            endif;

            ?>

            <div class="box-wrapper">
                <div class="people">
                    <div class="picture">
                        <img src="<?php echo $commercial_avatar; ?>" alt="" srcset="">
                    </div>
                    <div class="details">
                        <div class="name"><?php echo "$commercial_firstname $commercial_lastname"; ?></div>
                        <div class="job">Commercial</div>
                    </div>
                    <div class="contact">
                        <a href="mailto:<?php echo $commercial_email; ?>" class="email">
                            <i class="fas fa-lg fa-envelope"></i>
                        </a>
                        <a href="tel:<?php echo $commercial_tel; ?>" class="phone">
                            <i class="fas fa-lg fa-phone"></i>
                        </a>
                    </div>
                </div>
                <div class="people">
                    <div class="picture">
                        <img src="<?php echo $cdp_avatar; ?>" alt="" srcset="">
                    </div>
                    <div class="details">
                        <div class="name"><?php echo "$cdp_firstname $cdp_lastname"; ?></div>
                        <div class="job">Chef de projet</div>
                    </div>
                    <div class="contact">
                        <a href="mailto:<?php echo $cdp_email; ?>" class="email">
                            <i class="fas fa-lg fa-envelope"></i>
                        </a>
                        <a href="tel:<?php echo $cdp_tel; ?>" class="phone">
                            <i class="fas fa-lg fa-phone"></i>
                        </a>
                    </div>
                </div>
            </div>

            <?php
        }

        /**
         * Forfaits
         */
        public function pilotin_forfait() {

            // Option
            $client_id     = get_option( 'piloboard-client-id' );
            $has_transient = get_transient( 'piloboard-forfait' );

            // Has Transient stop api call
            if ( $has_transient ) :
                $acf_meta = $has_transient;
            else :
                // Api Call
                $url      = 'https://piloboard.pilot-in.net/wp-json/acf/v3/client/' . $client_id . '/';
                $response = $this->api_call( $url );
                // Fields
                if ( $response ) :
                    $acf_meta = $response['acf'];
                    set_transient( 'piloboard-forfait', $acf_meta, 2 * HOUR_IN_SECONDS );
                endif;
            endif;

            // Fields
            $maintenance   = $acf_meta['maintenance'];
            $tma           = $acf_meta['tma'];
            $tma_mensuelle = null;
            if ( $tma ) :
                $tma_mensuelle = $acf_meta['tma_mensuelle'];
            endif;
            $hebergement = $acf_meta['hosting'];
            ?>

            <div class="box-wrapper">
                <div class="box-item">
                    <div class="picture">
                        <i class="fad fa-2x fa-repeat-alt"></i>
                    </div>
                    <div class="details">
                        <div class="type">Maintenance</div>
                        <div class="info"><?php echo $maintenance ? 'Votre site est automatiquement mis à jour par Pilot\'in.' : 'Vous n\'avez pas de maintenance.'; ?></div>
                    </div>
                </div>
                <div class="box-item">
                    <div class="picture">
                        <i class="fad fa-2x fa-tools"></i>
                    </div>
                    <div class="details">
                        <div class="type">TMA (Tierce Maintenance Applicative)</div>
                        <div class="info"><?php echo $tma_mensuelle ? 'Forfait : ' . $tma_mensuelle . 'h / mois.' : 'Vous n\'avez pas de forfait de TMA.'; ?></div>
                    </div>
                </div>
                <div class="box-item">
                    <div class="picture">
                        <i class="fad fa-2x fa-server"></i>
                    </div>
                    <div class="details">
                        <div class="type">Hébergement</div>
                        <div class="info"><?php echo $hebergement ? 'Votre site est hébergé chez Pilot\'in.' : 'Votre site n\'est pas hébergé chez Pilot\'in.'; ?></div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * F.A.Q
         */
        public function pilotin_faq() {

            // Option
            $has_transient = get_transient( 'piloboard-faq' );

            // Has Transient stop api call
            if ( !$has_transient ) :

                // Api Call
                $url      = 'https://piloboard.pilot-in.net/wp-json/acf/v3/faq/';
                $response = $this->api_call( $url );

                // Fields
                if ( !$response ) :
                    return;
                endif;

                $faq_list_ids = wp_list_pluck( $response, 'id' );
                $faq_final    = array();
                foreach ( $faq_list_ids as $faq_id ) :
                    $faq_obj        = $this->api_call( 'https://piloboard.pilot-in.net/wp-json/wp/v2/faq/' . $faq_id );
                    $faq_title      = $faq_obj['title']['rendered'];
                    $faq_content    = $faq_obj['content']['rendered'];
                    $faq_tags       = $faq_obj['tags'];
                    $faq_final_tags = array();
                    // Tags
                    foreach ( $faq_tags as $faq_tag_id ) :
                        $faq_tag_obj      = $this->api_call( 'https://piloboard.pilot-in.net/wp-json/wp/v2/tags/' . $faq_tag_id );
                        $faq_tag_name     = $faq_tag_obj['name'];
                        $faq_final_tags[] = $faq_tag_name;
                    endforeach;
                    $faq_final[] = array(
                        'title'   => $faq_title,
                        'content' => $faq_content,
                        'tags'    => $faq_final_tags,
                    );
                endforeach;
                set_transient( 'piloboard-faq', $faq_final, 2 * HOUR_IN_SECONDS );

            else :

                $faq_list_ids = $has_transient;

            endif;

            if ( $faq_list_ids ) : ?>
                <div class="box-wrapper">
                    <?php
                    foreach ( $faq_list_ids as $faq ) :
                        if (!function_exists('acf_maybe_get')){
                            $faq_title = $faq['title'];
                            $faq_content = $faq['content'];
                            $faq_tags = $faq['tags'];
                        } else {
                            $faq_title = acf_maybe_get( $faq, 'title' );
                            $faq_content = acf_maybe_get( $faq, 'content' );
                            $faq_tags = acf_maybe_get( $faq, 'tags' );
                        }
                        ?>
                        <div class="box-item">
                            <div class="details">
                                <div class="title">
                                    <?php echo $faq_title; ?>
                                    <div class="picture">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                </div>
                                <?php
                                // Tags
                                if ( $faq_tags ) :
                                    foreach ( $faq_tags as $faq_tag ) : ?>
                                        <div class="tag"><?php echo $faq_tag; ?></div>
                                    <?php
                                    endforeach;
                                endif; ?>
                                <div class="content">
                                    <?php echo $faq_content; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php
        }

        /**
         * Display Option Page Content
         */
        public function display_content() {

            $piloboard_licence = get_option( 'piloboard-licence' );
            $status            = '';
            if ( $piloboard_licence ) :
                $status = 'disabled';
            endif;
            ?>
            <div class="piloboard-wrapper">
                <div class="content">
                    <svg class="logo" xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="none" viewBox="0 0 511 512">
                        <path fill="#2139E1"
                              d="M255.224 0C114.571 0 0 114.859 0 256c0 68.867 26.57 133.354 74.837 181.536C122.858 485.475 186.969 512 255.224 512c68.499 0 132.853-26.525 180.876-74.464C484.122 389.354 510.449 325.11 510.449 256c0-68.38-26.327-132.624-74.349-181.05C387.834 26.769 323.723 0 255.224 0zM123.102 389.354c-31.446-31.635-50.216-72.517-54.116-116.806 89.463-34.556 146.26-46.966 186.97-41.369 7.8 1.216 11.944 2.433 14.138 3.407 0 5.11-1.95 15.33-3.169 22.387-4.875 25.065-11.457 59.377 6.826 93.932 14.382 27.255 40.953 48.182 81.418 64.243-29.496 18.738-63.867 28.715-100.188 28.715-49.729 0-96.532-19.468-131.879-54.509zm287.158-27.742c-41.196-11.437-67.523-26.281-76.299-42.829-7.069-13.14-3.656-30.418 0-48.912 4.388-22.631 9.751-50.86-7.313-74.951-11.944-16.791-31.933-27.011-60.942-31.148-47.534-6.814-104.088 2.92-187.7 32.852 24.62-74.464 94.825-128.487 177.218-128.487 50.216 0 97.263 19.468 132.366 54.996 35.102 35.285 54.604 82.738 54.604 132.867 0 38.449-11.213 74.951-31.934 105.612z"/>
                    </svg>
                    <form class="piloboard-form-save-licence" action="#" method="post">
                        <input type="text" name="piloboard_licence" minlength="32" maxlength="32" id="piloboard_licence" value="<?php echo $piloboard_licence ? $piloboard_licence : ''; ?>"
                               placeholder="XXXXXXXXXXXXXXX" required <?php echo $status; ?>>
                        <?php if ( !$piloboard_licence ) : ?>
                            <button type="submit" value="Enregistrer" <?php echo $status; ?>>Enregistrer</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php
        }

        /**
         * Enqueue Back-End
         */
        public function enqueue_custom_styles() {

            // JS
            wp_enqueue_script( 'piloboard-script', plugin_dir_url( __FILE__ ) . '../assets/js/main.js', array( 'jquery' ), 1.0, false );
            wp_enqueue_script( 'piloboard-font-awesome-kit', 'https://kit.fontawesome.com/f609582e69.js', array(), 1.0, false );

            // AJAX
            wp_localize_script(
                'piloboard-script',
                'admin',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'ajax-nonce' ),
                )
            );

            // CSS
            wp_enqueue_style( 'piloboard-style', plugin_dir_url( __FILE__ ) . '../assets/style/main.css', array(), filemtime( plugin_dir_path( __FILE__ ) . '../assets/style/main.css' ), 'all' );

        }

        /**
         * Send Licence
         */
        public function send_licence() {

            // Prevent non authorized user to make action
            if ( !is_user_logged_in() && ( !current_user_can( 'edit_plugins' ) && !current_user_can( 'activate_plugins' ) ) ) :
                return;
            endif;

            $piloboard_licence = $_POST['licence']; // phpcs:disable
            if ( $piloboard_licence ) :

                // Save Licence as option
                add_option( 'piloboard-licence', $piloboard_licence );

                $username = 'cabin';
                $password = '&v2XpTCThGP3iDFuaOK%d43N';
                $url      = 'https://piloboard.pilot-in.net/wp-json/piloboard/v1/licence/id=' . $piloboard_licence;
                $args     = array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
                    ),
                );
                $request  = wp_remote_request( $url, $args );
                if ( $request ) :
                    $body        = wp_remote_retrieve_body( $request );
                    $body_decode = json_decode( $body, true );
                    $client_id   = $body_decode['ID'];

                    // Save clientID as option
                    add_option( 'piloboard-client-id', $client_id );

                endif;

                wp_send_json_success( $piloboard_licence );
            endif;

        }

    }
}

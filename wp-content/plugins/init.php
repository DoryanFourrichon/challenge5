<?php
defined( 'ABSPATH' ) || exit;

/**
 * Include if file exists
 *
 * @param string $filename
 */
function pip_addon_include( $filename = '' ) {
    $file_path = PIP_ADDON_PATH . ltrim( $filename, '/' );
    if ( file_exists( $file_path ) ) {
        include_once( $file_path );
    }
}

/**
 * Check if Pilo'Press is activated
 */
add_action( 'after_plugin_row_' . PIP_ADDON_BASENAME, 'pip_addon_plugin_row', 5, 3 );
function pip_addon_plugin_row( $plugin_file, $plugin_data, $status ) {

    // If PiloPress is activated, return
    if ( pip_addon()->has_pip() ) {
        return;
    }

    ?>

    <style>
        .plugins tr[data-plugin='<?php echo PIP_ADDON_BASENAME; ?>'] th,
        .plugins tr[data-plugin='<?php echo PIP_ADDON_BASENAME; ?>'] td {
            box-shadow: none;
        }

        <?php if( isset( $plugin_data['update'] ) && !empty( $plugin_data['update'] ) ): ?>

        .plugins tr.pilopress-pilotin-addon-plugin-tr td {
            box-shadow: none !important;
        }

        .plugins tr.pilopress-pilotin-addon-plugin-tr .update-message {
            margin-bottom: 0;
        }

        <?php endif; ?>
    </style>

    <tr class="plugin-update-tr active pilopress-pilotin-addon-plugin-tr">
        <td colspan="3" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p><?php _e( "Pilo'Press - Pilot'in Addon requires Pilo'Press.", 'pip-addon' ); ?></p>
            </div>
        </td>
    </tr>

    <?php

}

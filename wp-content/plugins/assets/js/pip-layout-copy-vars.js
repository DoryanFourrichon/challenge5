jQuery( document ).ready(
    function ( $ ) {

        // Return if not on right page
        if ( $( '#pip_layout_settings' ).length === 0 ) {
            return;
        }

        let $css_vars = $( '.acf-field-pip-layout-var' ).find( '.acf-input table tbody tr.acf-row:not(.acf-clone)' );

        if ( $css_vars.length === 0 ) {
            return;
        }

        let firstArray = [];

        // Create button
        $( '<li><a id="copy-vars" href="#" style="background: #2271b1;color: white;">Copy variables</a><textarea style="position:absolute;top:-9999px;left:-9999px;" id="paste-vars"></textarea></li>' ).appendTo( '#pip_layout_settings .acf-tab-group' );

        let $copy_btn  = $( '#copy-vars' ),
            $paste_btn = $( '#paste-vars' );

        if ( $copy_btn.length === 0 || $paste_btn.length === 0 ) {
            return;
        }

        $css_vars.each(
            function () {
                var $this = $( this );
                var key   = $this.find( '.acf-field-pip-layout-var-key input' ).val();
                var value = $this.find( '.acf-field-pip-layout-var-value input' ).val();
                firstArray.push( ' - __' + key + '__ : ' + value );
            },
        );

        let finalArray = firstArray.join( '\n' );
        $paste_btn.val( finalArray );

        $copy_btn.click(
            function () {
                $paste_btn.select();
                document.execCommand( 'copy' );
                alert( 'Variables copiées !' );
            },
        );
    },
);
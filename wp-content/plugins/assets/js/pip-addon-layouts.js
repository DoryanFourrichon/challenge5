jQuery(function ($) {

    // ACF only
    if (typeof acf != 'undefined') {

        // Add font awesome icon shortcode
        pip_wysiwyg_add_font_awesome_icon_shortcode();

        // Replace sensible elements present in preview to avoid conflict (page saving due to inner forms, overlapping elements...etc)
        pip_sanitize_layout_preview();

        // Apply fix again when preview is refreshed
        acf.addAction('acfe/fields/flexible_content/preview', function () {
            setTimeout(pip_sanitize_layout_preview, 450);
        });

    }

    // Prevent accidentally clicking in a layout link on preview mode
    $(document).on('click', '.-preview a', pip_prevent_link_click_in_preview);
    function pip_prevent_link_click_in_preview(event) {
        event.preventDefault();
    }

    // Replace sensible elements present in preview to avoid conflict (page saving due to inner forms, overlapping elements...etc)
    function pip_sanitize_layout_preview() {

        const $previews_window = $('.-preview');
        if (!$previews_window.length) {
            return;
        }

        // previews found
        $previews_window.each(
            function (index) {

                const $preview = $(this);

                // Remove "links / scripts / iframes / inputs hidden"
                const $els_to_remove = $preview.find('link, input[type="hidden"], script, iframe');
                if ($els_to_remove.length) {
                    $els_to_remove.each(
                        function (index) {
                            const $el_to_remove = $(this);
                            console.log($el_to_remove);
                            console.log('removed.');
                            $el_to_remove.remove();
                        },
                    );
                }

                // Replace "Forms / Textarea / Buttons"
                const $forms = $preview.find('form, textarea, button[type="submit"]');
                if ($forms.length) {
                    $forms.each(
                        function (index) {
                            const $form = $(this);
                            $form.replaceWith('<div class="' + $form.attr('class') + '">' + $form.html() + '</div>');
                        },
                    );
                }

                // Replace "Inputs submit"
                const $inputs_submit = $preview.find('input[type="submit"]');
                if ($inputs_submit.length) {
                    $inputs_submit.each(
                        function (index) {
                            const $input = $(this);
                            // Replace input
                            $input.replaceWith('<div class="' + $input.attr('class') + '">' + $input.val() + '</div>');
                        },
                    );
                }

                // Replace "other inputs"
                const $inputs = $preview.find('input:not([type="hidden"])');
                if ($inputs.length) {
                    $inputs.each(
                        function (index) {
                            const $input = $(this);
                            // Replace input
                            $input.replaceWith('<div class="' + $input.attr('class') + ' bg-white p-4 inline-flex justify-center">' + $input.attr('placeholder') + '</div>');
                        },
                    );
                }

                // Replace "Select"
                const $selects = $preview.find('select[name]');
                if ($selects.length) {
                    $selects.each(
                        function (index) {
                            const $select = $(this);
                            $select
                                .removeAttr('name')
                                .removeAttr('required')
                                .removeAttr('id');
                        },
                    );
                }

                // Replace "fixed" class by "absolute" class (to avoid overlapping admin UI)
                const $fixed_els = $preview.find('.fixed');
                if ($fixed_els.length) {
                    $fixed_els.each(
                        function (index) {
                            const $fixed_el = $(this);
                            $fixed_el
                                .removeClass('fixed')
                                .addClass('absolute')
                        },
                    );
                }

            },
        );

    }

    // Replace form & input by div element to avoid broken content edition
    function pip_wysiwyg_add_font_awesome_icon_shortcode() {

        /**
         * Get attribute from shortcode text
         *
         * @param str
         *
         * @param name
         * @returns {string}
         */
        var getAttr = function (str, name) {
            name = new RegExp(name + '=\"([^\"]+)\"').exec(str);
            return name ? window.decodeURIComponent(name[1]) : '';
        };

        /**
         * Build shortcode
         *
         * @param event
         *
         * @param attributes
         * @returns {string}
         */
        var build_shortcode = function (event, attributes) {
            // Open shortcode
            var out = '[' + attributes.tag;

            // Add attributes to shortcode
            $.each(
                event.data,
                function (key, value) {
                    if (value === false) {
                        value = '';
                    }
                    out += ' ' + key + '="' + value + '"';
                },
            );

            // Close shortcode
            out += ']';

            return out;
        };

        // Wait for TinyMCE to be ready
        $(document).on(
            'tinymce-editor-setup',
            function (event, editor) {

                // Add "Icon - Font Awesome" to PIP shortcodes menu
                acf.addFilter(
                    'pip/tinymce/shortcodes',
                    function (shortcodes, event, editor) {

                        if (!shortcodes) {
                            return shortcodes;
                        }

                        // Add shortcode
                        var shortcode_is_already_added = false;
                        $.each(
                            shortcodes,
                            function (key, shortcode) {
                                if (shortcode.tag === 'pip_icon_fa') {
                                    shortcode_is_already_added = true;
                                    return true;
                                }
                            },
                        );

                        // Skip if shortcode already added
                        if (shortcode_is_already_added) {
                            return shortcodes;
                        }

                        // Get theme colors
                        var colors = acf.get('custom_colors');

                        // Icon - Font Awesome shortcode
                        var pip_icon_fa = {
                            text: 'Icon - Font Awesome',
                            tag: 'pip_icon_fa',
                            name: 'Add icon',
                            body: [
                                {
                                    label: 'Style',
                                    name: 'style',
                                    type: 'listbox',
                                    values: [
                                        { text: 'Solid', value: 'fas' },
                                        { text: 'Regular', value: 'far' },
                                        { text: 'Light', value: 'fal' },
                                        { text: 'Duotone', value: 'fad' },
                                        { text: 'Brands', value: 'fab' },
                                    ],
                                },
                                {
                                    label: 'Icons',
                                    name: 'icon',
                                    type: 'textbox',
                                    value: 'fa-paper-plane',
                                },
                                {
                                    label: 'Class',
                                    name: 'class',
                                    type: 'textbox',
                                    value: 'fa-2x',
                                },
                                {
                                    label: 'Link url (clickable icon)',
                                    name: 'link',
                                    type: 'textbox',
                                    value: '',
                                },
                                {
                                    label: 'Link target',
                                    name: 'target',
                                    type: 'listbox',
                                    values: [
                                        { text: 'Same page', value: '_self' },
                                        { text: 'New page', value: '_blank' },
                                    ],
                                },
                            ],
                            onclick: function (event) {
                                var attributes = event.control.settings;

                                // If no tag, return
                                if (_.isUndefined(attributes.tag)) {
                                    return;
                                }

                                // Get attributes
                                var window_title = !_.isUndefined(attributes.name) ? attributes.name : 'Add icon';

                                // Modal
                                editor.windowManager.open(
                                    {
                                        title: window_title,
                                        body: attributes.body,
                                        onsubmit: function (event) {
                                            editor.insertContent(build_shortcode(event, attributes));
                                        },
                                    },
                                );
                            },
                        };

                        shortcodes.push(pip_icon_fa);

                        return shortcodes;
                    },
                );

            },
        );
    }

});

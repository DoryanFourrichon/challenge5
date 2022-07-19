// TODO: Change layout name "LayoutModel" & layout class "layout-model"
if (typeof PIPLayout === 'function' && typeof LayoutModel == 'undefined') {

    // Section Name
    const layoutName = '.layout-model';
    let layout;

    // Section Class
    class LayoutModel extends PIPLayout {
        constructor($) {

            // Properties
            super(); // Allow the use of PIPLayout properties & methods (PIPLayout is located in /pilopress-addon/assets/js/pip-layout-class.js)
            this.layoutName = layoutName; // Selector of the layout
            this.$els = $(this.layoutName); // jQuery element of the layout(s)
            this.libs = []; // External JavaScript libraries you will use in this layout

            // Store current scope into a variable to use it in other context (events...etc)
            layout = this;

            // Requirements
            if (!this.layoutExists() || !this.libsLoaded()) {
                return;
            }

        }

        // Your code starts here!
        doStuff() {

        }

        // 1. Execute code as soon as possible
        onInit() {
            layout.doStuff();
        }

        // 2. Execute code when DOM is ready
        onReady() {

        }

        // 3. Execute code when DOM is totally loaded
        onLoad(ev) {

        }

    }

    // Init class - Don't touch this part except to change class name (ex: "new LayoutModel($)")
    jQuery(function ($) {
        const layout = new LayoutModel($);
        layout.registerLayout(layout);
    });

}

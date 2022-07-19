// Layout class base
if (typeof PIPLayout == 'undefined') {

    // Get jQuery object and store it in global for further use
    const $ = window.jQuery;
    window.$ = $;

    class PIPLayout {
        constructor() {

            // Properties
            this.layoutName = 'section'; // Selector of the layout
            this.$els = $(this.layoutName); // jQuery element of the layout
            this.libs = []; // External JavaScript libraries
            this.instances = []; // Instances of initialized JavaScript libraries
            this.isDebug = window.location.search.substr(1) === 'debug'; // Add "?debug" in the url to get logs

            // Requirements
            if (!this.layoutExists() || !this.libsLoaded()) {
                return;
            }

            // Methods
            this.onInit();

            // Events
            $(document).ready(this.onReady);
            $(window).on('load', this.onLoad);

        }

        // Layout required
        layoutExists() {

            // Section not found
            if (!this.$els.length) {
                if (this.isDebug) {
                    console.error(`PiloPress Layout: section "${this.$els.className}" doesn't exists.`);
                }
                return false;
            }

            return true;
        }

        // Libs required
        libsLoaded() {

            // No libs to load
            if (!this.libs.length) {
                return true;
            }

            // Check if all libs are loaded
            this.libs.forEach((lib) => {
                if (typeof window[lib] == 'undefined') {
                    if (this.isDebug) {
                        console.error(`PiloPress Layout: library "${lib}" is not loaded.`);
                    }
                    return false;
                }
            });

            return true;

        }

        // Execute code as soon as possible
        registerLayout(layoutInstance) {

            // Check if pipAddon is initialized
            if (typeof pipAddon == 'undefined') {
                return console.error('PiloPress Layout: pipAddon is not available to register this layout.');
            }

            // Add instance with all layouts instances to access it if needed
            pipAddon.layoutInstances = pipAddon.layoutInstances || [];
            pipAddon.layoutInstances.push(layoutInstance);

        }

        // Execute code as soon as possible
        onInit() {

        }

        // Execute code when DOM is ready
        onReady() {

        }

        // Execute code when DOM is totally loaded
        onLoad(ev) {

        }

    }

    // Register class in DOM
    window.PIPLayout = PIPLayout;

}

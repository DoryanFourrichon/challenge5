# Pilo'Press - Addon

Quick start config and helpers we use at Pilot'in for WordPress & Pilo'Press.

## Features included:
- **Login**:
  - Enhance login page visual _(logo + title)_
  - Change login url to `/connect-in` _(instead of `/wp-login.php`)_

- **Admin**:
  - Add longer expiration for logged-in user "**cabin**".
  - Remove "**Comments**" menu.
  - Move native post-types _(Post, Page)_ in admin menu to the top.

- **Menu**:
  - Add a field to **set a Font Awesome icon** on a **menu item**.
  - Add default TailwindCSS classes on parent menu items & submenus elements.
 
- **Post**:
  - Add a meta `reading_time` to have a reading time estimation of the content.
  - Set only "**Clone**" quick action for Yoast Duplicate Post

- **Front**:
  - Remove non-useful WordPress assets _(emoji, comments...etc)_ 
  - Add `.htaccess` optimization for cache rules & preventing direct access to sensible files _(`wp-config.php`, `debug.log`...)_.

- **ACF**:
  - Add an option page "**Pilo'Press > Settings**".
  - Add a field to set **Google Maps API key** globally for ACF Google Maps field.
  - Add a field to set **Google Tag Manager key** & enqueue automatically Google Tag Manager script.
  - Add new field type "**Menus**" & "**Menu items**".
  - **Set Font Awesome** to **version 5** by default in ACF Font Awesome plugin.

- **ACFE**:
  - Enable "**ACF Extended: Single Meta**" _(for better DB perfs regarding Pilo'Press flexible)_
  - Enable "**ACF Extended: Super Developer mode**" for `cabin` user

- **Pilo'Press**:
  - Add default config for TailwindCSS config into Pilo'Press

- **WooCommerce**:
  - Load enhanced WooCommerce templates directly from the addon.

- **Shortcodes**:
  - Add an **Font Awesome icon** shortcode.
  - Enhance **Gallery** shortcode with a lightbox when clicking on images.

- **SEO**:
  - Enhance `robots.txt` content
  - Disable Yoast SEO Author archive by default.

- **Post-types**:
  - Add "**soumission**" post-type json to be imported in `/post-types/` folder.

- **Forms**:
  - Add default ACFE "**Formulaire de contact**" form json to be imported in `/forms/` folder.

- **Helpers**

## Requirements:

This plugin requires those others plugins in order to work correctly:
- [**Advanced Custom Fields PRO**](https://www.advancedcustomfields.com/pro/)
- [**Advanced Custom Fields: Extended**](https://wordpress.org/plugins/acf-extended/)
- [**Pilo'Press**](https://wordpress.org/plugins/pilopress/)

## Plugins included inside this addon:

- **Classic Editor**
- **WPS Hide Login**
- **Bottom Admin Bar**

# Pilo'Press Private Starter theme

This theme is based on the **[Pilo'Press Starter Theme](https://github.com/Pilot-in/PiloPress-Starter-Theme)** but with specific config & layouts for faster development.

## Requirements

- [PHP](http://php.net/) >= 7.2
- [WordPress](https://wordpress.org/) >= 5.0

## Directory structure

```
PiloPress-Private-Starter-Theme
├── acf-json/                              # Where ACF field groups JSON are automatically stored
├── includes/                              
│   ├── class-project.php                  # Add your actions / filters here, register your post-types...etc
├── pilopress/                             # Pilo'Press main folder.
│   ├── assets/                            # Pilo'Press TailwindCSS generated files (you shouldn't modify those files)
│   └── layouts/                           # Pilo'Press layouts folder.
│   │   ├── a-layout-name/
│   │   │   ├── group-xxxxxx.json          # ACF Layout field group.
│   │   │   ├── a-layout-name.php          # Layout template / view.
│   │   │   ├── a-layout-name.css          # Layout stylesheet.
│   │   │   ├── a-layout-name.js           # Layout javascript.
│   │   │   └── config-a-layout-name.php   # (optional) Layout config / controller. (actions / filters dedicated to this layout...etc)
├── functions.php                          # Bootstrap theme (used to require your files in /includes)
├── screenshot.png                         # Theme screenshot.
├── style.css                              # Theme stylesheet.
└── ...
```

### Notable directories / files

#### `functions.php`

This is the **main bootstrap file**. You should only have `require_once` statements into this file.

#### `style.css`

This is the **main style file**. If you have specific style for this project, you can add it here.  
**It's used by Pilo'Press** into the TailwindCSS compilation to generate **TailwindCSS optimized back-end & front-end build**.  
You can **consider this file to be a PostCSS file** & you can use TailwindCSS features like `@apply` inside it.  
**This file is not directly enqueued in front**, so after any changes to this file, you must go to **Pilo'Press > Styles > Update & Compile**.

#### `includes/class-project.php`

This is the **main class file**. **All actions & filters** should be added in this file.

#### `includes/class-*.php`

If you have **more than 15/20 actions & filters** in your `class-project.php` file, we recommand splitting those actions & filters  
into another class file _(for example if you have a lot of WooCommerce actions & filters, create a `class-woocommerce.php` file)_  
Don't forget to `require_once` your new files inside your `functions.php` file.

#### `includes/helpers.php`

_(optinal)_ If you need / want to create a **PHP helper file**, add it here. Helper files should include __function definitions only__. See below for information on where to put actions, filters, classes etc.

#### `pilopress/layouts/`

While views that follow the WordPress template hierarchy should go in the theme root directory (e.g. `index.php`, `searchform.php`, `archive-post.php` etc.), others should go in the following directories:
1. `pilopress/layouts/**/*.php` - **Pilo'Press layouts** views
2. `woocommerce/**/*.php` - **WooCommerce** template parts

Avoid adding any PHP logic in any of these templates, unless it pertains to layouting. Business logic should go into:
- Project classes (`includes/class-*.php`)
- Helper file (`includes/helpers.php`) _(optional)_

## Bonus - To compile TailwindCSS locally

1. Run `npm install`
2. Click on "**Update and build**" button in **Pilo'Press > Styles** admin menu
3. Run `npm run build` to build front and admin bundle styles

You can run `npm run build-front` or `npm run build-admin` to build desired stylesheet.

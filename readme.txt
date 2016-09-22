=== Plugin Name ===

Contributors: pcfreak30
Donate link: http://www.paypal.me/pcfreak30
Tags: optimize, wp-rocket, footer javascript
Requires at least: 4.2.0
Tested up to: 4.6.1
Stable tag: 1.1.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin to force all JS to the footer including inline scripts. Depends on WP-Rocket

This is NOT an official addon to WP-Rocket!

== Description ==

This plugin will combine all inline and external JS in the order found on the page and save it to WP-Rocket's cache folder as a new file. All *localized* scripts are excluded and combined to one script, placed before the external script tag.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/rocket-footer-js` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
4. Clear WP-Rocket cache and view HTML source!

== Changelog ==

### 1.1.13 ###

* Ensure zxcvbn is loaded normally and not async

### 1.1.12 ###

* Exclude js template script tags

### 1.1.11 ###

* Check for sourcemaps and add a new line to prevent syntax errors

### 1.1.10 ###

* Check for off in display_errors

### 1.1.9 ###

* Catch errors if WP_Error is returned or status code is not 200 or 304 or its empty
* Log errors if debug mode is enabled or PHP display_errors is enabled
* Disable minify when debug is on regardless of settings
* Log processed scripts in debug mode
* Move query string check to only run for local files

### 1.1.8 ###

* Web fetch dynamic scripts being defined as not having a JS extension
* Add regex to remove broken conditional comments out of inline js

### 1.1.7 ###

* Add constant DONOTMINIFYJS and function is_rocket_post_excluded_option to minify status check

### 1.1.6 ###

* If file is external, we do not want to treat the response as a filesystem path
* Always set the url domain back to home_url() because it will need to be that even if the original is a CDN or not

### 1.1.5 ###

* Use home URL and ABSPATH for the site root and not assume everything is in wp-content

### 1.1.4 ###

* Use a http_build_url shim as a fallback instead of deactivating with an error

### 1.1.3 ###

* Set main script tag to async

### 1.1.2 ###

* Minified wrong JS buffer for inline JS
* Don't prepend semicolon since its already conditionally prepended for inline JS

### 1.1.1 ###

* Add detection for PHP HTTP PECL extension
* Update code commentation and PHPDoc blocks

### 1.1.0 ###

* Changed logic to disable minify setting on front end and combine all scripts + minify if option is set (excluding localized scripts) to a new file in minify cache folder. File name will have user ID if logged in to be unique.
* Keep application/ld+json in the header

### 1.0.2 ###

* Exclude JS extension from slug name and ensure remote file is saved with a JS extension

### 1.0.1 ###

* Check for CDN files in remote tags and convert back to a local filename for minification
* Do variable cleanup

### 1.0.0 ###

* Initial version
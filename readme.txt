=== Plugin Name ===

Contributors: pcfreak30
Donate link: http://www.paypal.me/pcfreak30
Tags: optimize, wp-rocket, footer javascript
Requires at least: 4.2.0
Tested up to: 4.2.2
Stable tag: 1.1.2
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
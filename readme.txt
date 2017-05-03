=== Plugin Name ===

Contributors: pcfreak30
Donate link: http://www.paypal.me/pcfreak30
Tags: optimize, wp-rocket, footer javascript, lazy load, async js, async javascript, speed
Requires at least: 4.2.0
Tested up to: 4.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin to do a better job with your scripts and improve lazy loading. Depends on WP-Rocket

This is NOT an official addon to WP-Rocket!

== Description ==

This plugin will do the following:

* Process all inline and external JS to one file, not multiple, and put at the footer with async on
* Put all *localized* scripts together before the primary script above
* Automatically optimize popular 3rd party services including:
 * Tawk.to
 * WP Rockets lazyload
 * Google Analytics
 * Double Click Google Analytics
 * Avvo.com Tracking
 * Pushcrew Tracking
* Automatically lazy load popular widgets if https://wordpress.org/plugins/lazy-load-xt/ or https://wordpress.org/plugins/a3-lazy-load/ are active. Services include:
 * Google Maps with Avada theme
 * All Facebook social widgets
 * All Twitter social widgets
 * All Google Plus social widgets
 * All Google Adsense advertisements
 * Tumbler
 * Amazon Ads
 * Stumble Upon
 * VK.com
 * WooCommerce Social Media Share Buttons plugin
 * Any iframe

If you need dedicated/professional assistance with this plugin or just want an expert to get your site to run the fastest it can be, you may hire me at [Codeable](https://codeable.io/developers/derrick-hammer/?ref=rvtGZ)

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/rocket-footer-js` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
4. Clear WP-Rocket cache and view HTML source!

== Changelog ==

### 1.4.6 ###

* Strip returns in rocket_footer_js_rewrite_js_loaders
* Improve Google Analytics to conditionally handle ssl
* Bug fix hanging of Facebook Pixel fbq calls
* Add Pushcrew Tracking
* Ensure Facebook SDK is only loaded 1 time
* Refactor Google Plus to use simpler xpath queries and set a dummy pixel image to emsure it is picked up by lazy load
* Add support for Google Plus loaded via JS
* Improve twitter regex
* Add Tumbler support
* Improve Google Adsense support and skip ads where there is no ins tag as this is likely a full page or alternate ad
* Add Amazon Ads support
* Add Stumble Upon support
* Add VK.com support
* For Google Adsense, Amazon Ads, and Google Plus, if lazy load is off and the scripts are normal tags, flag to not minify so the scripts are not broken
* Add support for WooCommerce Social Media Share Buttons plugin
* Use WP_DEBUG_LOG over WP_DEBUG in rocket_footer_js_debug_enabled
* Fix logic in rocket_footer_js_debug_enabled that may cause debug to be on by mistake

### 1.4.5 ###

* Improve facebook pixel support to prevent possible runtime errors

### 1.4.4 ###

* Add support for Avvo.com tracking
* Ensure zxcvbn password meter is not changed on login and signup pages

### 1.4.3 ###

* Update Page Links To compatibility

### 1.4.2 ###

* Improve UTF-8 character handling
* Add support for googleanalytics plugin
* Improve GA regex
* Add compatibility with N2Extend framework

### 1.4.1 ###

* Add support for Sumo Me

### 1.4.0 ###

* Improve multi-line comment regex
* Rebuild cache system without using SQL

### 1.3.9 ###

* Extract and minify GA calls

### 1.3.8 ###

* Remove comments from js since JSMin doesn't do it by using a new function rocket_footer_js_minify
* Run rocket_footer_js_process_remote_script and rocket_footer_js_process_local_script when using cached data as well
* If rocket_footer_js_process_remote_script/rocket_footer_js_process_local_script return a modified script, then use the original in the cache but minified so it gets processed again properly the next request
* Inline scripts were not getting cached
* Removed duplicate minify call for remote scripts
* Cache the tawk.to script
* Fix tawk.to minify call

### 1.3.7 ###

* Ensure home uses the active URL scheme
* Pass $tags_ref to rocket_footer_js_process_local_script not $tags
* Change rocket_footer_js_process_local_script signature to use $tags by reference
* Add support for Facebook Pixel
* Add support for Pixel Your Site plugin since it stores the pixel code in its own script
* Add support for Google Web Fonts JS loader

### 1.3.6 ###

* Automatically lazy load iframes if they are not lazy loaded already

### 1.3.5 ###

* Ensure async attribute is compatible with XHTML

### 1.3.4 ###

* Ensure lazy load comments don't get stripped by html minify by using tag markers and doing a regex replacement after minification
* Improve Twitter regex to support another variation
* Improve Facebook regex to support another variation
* Add support for DoubleClick GA
* Add support for Google Adsense lazy loading

### 1.3.3 ###

* Add compatibility hack for older libxml
* Skip text/html scripts

### 1.3.2 ###

* Treat google maps as loading async with a typeof timer and load infobox async if it exists
* Check document.readyState to run map function in case the window load event already ran

### 1.3.1 ###

* Move debug code to rocket_footer_js_debug_enabled function
* Move web fetch code to rocket_footer_js_remote_fetch function
* Use rocket_add_url_protocol in rocket_footer_js_rewrite_js_loaders

### 1.3.0 ###

* Auto optimize Tawk.to, WP Rockets lazyload, and google analytics to use normal tags instead of javascript loaders so they can get minified
* If minify is enabled due to LazyLoadXT or A3_Lazy_Load support, then lazy load facebook, twitter, google plus widgets, and avada google maps (if Avada_GoogleMap exists and google maps is on)
* Enqueue LazyLoadXT widget extension if lazyload is enabled since lazy load plugins don't supply it
* Improve lazy load regex patterns
* Split minify to rocket_footer_js_process_remote_script and rocket_footer_js_process_locate_script functions with associated filters to hook into
* Minify emojione in tawk.to JS
* Add hook rocket_footer_js_rewrite_js_loaders to allow pre-processing before minification
* Add support for avada google maps lazy loading
* Remove duplicate google maps API scripts and prioritize the first one that has an API key
* Only lazy load google maps if there is any script content
* Added function rocket_footer_js_lazyload_script to reduce code duplication

### 1.2.3 ###

* Ensure url scheme is set correctly when converting from a CDN domain

### 1.2.2 ###

* Disable minify on AMP pages

### 1.2.1 ###

* Tested on WordPress 4.7
* Ensure PHP 5.3 compatibility

### 1.2.0 ###

* Correct/improve relative URL logic
* Prevent html from being minified before JS to prevent issues with detection
* Add new minify cache system to reduce computation time required to minify a page

**Notice: This new cache system could cause unknown issues. While it has been tested, not every situation can be accounted for. Contact me if you hit a problem.**

**Notice: Cache is stored in transients, so only a normal wp-rocket purge will clear everything**

### 1.1.16 ###

* Fix logic bug in data-no-minify check

### 1.1.15 ###

* Check for relative URL's
* Add compatibility support for "Page Links To" since it does naughty things with buffering

### 1.1.14 ###

* Bugfix fetching JS from filesystem with http\Url
* Add a newline into the automatic semicolon insertion for the case that the last text is a comment

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
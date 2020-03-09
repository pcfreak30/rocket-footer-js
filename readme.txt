=== Plugin Name ===

Contributors: pcfreak30
Donate link: http://www.paypal.me/pcfreak30
Tags: optimize, wp-rocket, footer javascript, lazy load, async js, async javascript, speed
Requires at least: 4.2.0
Tested up to: 5.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin to do a better job with your scripts and improve lazy loading. Depends on WP-Rocket

This is NOT an official addon to WP-Rocket!

== Description ==

**This is NOT an official addon to WP-Rocket!**

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
 * Clicky Tracking
 * Facebook Pixel Tracking
 * MCAfee Secure
 * Sumo Ne
 * Pingdom Prum
 * Google Tag Manager
 * Mouse Flow
 * Cornerstone Page Builder
* Automatically lazy load popular widgets if https://wordpress.org/plugins/lazy-load-xt/ or https://wordpress.org/plugins/a3-lazy-load/ are active. Services include:
 * Google Maps with Avada theme
 * All Facebook social widgets
 * All Twitter social widgets
 * All Google Plus social widgets
 * All Google Adsense advertisements
 * Google Re-captcha
 * Tumbler
 * Amazon Ads
 * Stumble Upon
 * VK.com
 * WooCommerce Social Media Share Buttons plugin
 * Any iframe
 * Blog Her Ads
 * Video embeds (click to activate)
 * Pin Interest

If you are looking for a professional team to get your WordPress site to run faster, check us out for our speed optimization services at [Rank Grow Digital](https://rankgrowdigital.com/)

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/rocket-footer-js` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
4. Clear WP-Rocket cache and view HTML source!

== Changelog ==

### 3.2.3 ###

* Bug: Fix typo in HubSpotForms module and fix broken code
* Bug: Handle edge case of HTML5 video not fully lazyloading due to jQuery not triggering on loadeddata event
* Feature: Add a pseudo css API to allow any elements background image to be lazyloaded
* Enhancement: Add filters around enqueuing auxiliary scripts & default to not load the imagefixes script so that modules can flag it on at runtime
* Enhancement: Add micro-optimization by serving preminified versions of polyfills and lazysizes
* Enhancement: Improve cache management by deleting cache files when the objects are purged to handle edge cases
* Enhancement: Add support for purging cache via cron, using rocket_footer_js_background_cache_purge_item_threshold filter, and preload cache if enabled after
* Enhancement: Videos Module: Only set src/data-src if this is a video. If video is autoplay before we process it, skip it
* Enhancement: Videos Module: Add vimeo support
* Compatibility: Videos Module: Add videojs compatibility
* Compatibility: Add visual composer compatibility to lazy load background images
* Compatibility: Add custom event polyfill
* Compatibility: Add compatibility with wp-rocket's lazyload

### 3.2.2 ###

* Enhancement: Skip processing background image if it is flagged for no lazy load
* Enhancement: Centralize multibyte encoding for entities into the DOMDocument class so that style tags do not get processed

### 3.2.1 ###

* Bug: For elementor module, if in admin, functions should still run
* Bug: Encode both scripts and styles in DOM processing due to further edge cases found
* Enhancement: Add a3 lazy load excludes as a consideration for determining if lazy load is on
* Enhancement: Defer init checks for Elementor modules so that lazy load excludes can be processed
* Enhancement: Add support for wp-rocket file excludes
* Enhancement: For elementor module, add an option for a video thumbnail to have a custom alt attribute
* Enhancement: For video lazyload, add support for custom alt attribute, and fall back to oembed title
* Enhancement: Ensure wp-rocket preload only runs on cron by conditionally deferring it
* Integration: Add lazyload compatibility for WP Ultimate Post Grid

### 3.2.0 ###

* Bug: Add more compatibility fixes with a3 lazy load
* Bug: Fix bugs with jQuery compatibility of lazySizes
* Bug: Don't try and do processing during JSON/API requests
* Bug/Enhancement: Misc fixes and improvements to the video lazyload
* Bug: Remove the preload functionality from lazySizes
* Feature: Integrate WebPExpress support to allow WebP to work with other integrations/modules and to enable a separate cache for webp when supported
* Feature: Enable lazyload to be managed per element inside the Elementor Editor
* Compatibility: Add compatibility css for elementor in the video lazyload
* Compatibility: Add more Fusion logo options to be processed by CDN
* Compatibility: Prevent divi from concatenating JS
* Integration: Add compatibility with smart slider for lazy load
* Integration: Add compatibility with EWWW to prevent image processing from running on LazyLoaded Video thumbnails due to the excessive processing time
* Integration: Add compatibility With Essential Addons for Elementor
* Integration: Ignore hubspot forms in JS rewrite module and add LazyLoad Compatibility with HubSpot
* Integration: Add lazyload compatibility with Fusion portfolio elements
* Integration: Add lazyload compatibility with Fusion's lazySizes
* Integration: Add compatibility with Rank Math SEO
* Enhancement: Add hooks to allow the minify cache key to be modified
* Enhancement: Ensure picturefill JS script is loaded if CSS plugin is not active
* Enhancement: Improve rendering of lazyloaded elements with lazySizes
* Enhancement: Enable the video thumbnail to be a responsive image based on all image sizes registered, and support conditionally lazyloading it. Also support integration with WEBP.
* Enhancement: Improve check for if lazy load should be enabled
* Enhancement: Add filter to bypass reading/saving of cache
* Enhancement: Refactor and improve the divi lazyload suypport for videos
* Enhancement: Add webp support to MetaSlider
* Enhancement: Add compatibility with the wp-rocket preloader to ensure that it will run
* Enhancement: If the wp-rocket preloader is on, then run the preloader when site cache is cleared
* Enhancement: Clear a post cache when the post is saved
* Enhancement: Add support for a lazyloaded video thumbnail to not be lazyloaded via a data attribute, and expose that in the elementor editor
* Enhancement: Allow a lazyloaded video thumbnail to have it's responsive size manually set and expose that in the Elementor editor

### 3.1.2 ###

* Compatibility: Remove lazy load compatibility script for master slider as it is no longer needed

### 3.1.1 ###

* Bug: Verify that the found background properties have settings in elementor compatibility module
* Bug: Add background lazyload attribute to lazy load compatibility code
* Enhancement: Add font display swap to TypeKit module

### 3.1.0 ###

* Bug: Fix lazy load CSS to not be position absolute
* Bug: Hash the url without possible use of a CDN
* Bug: Fix gravity forms recaptcha lazy load support
* Enhancement: Migrate from lazyLoadXT to lazysizes for increase performance and less bugs with lazy load. For technical details the "intersection observer" version is used with polyfill's
* Compatibility: Fix with wp-rocket 3.1.x to prevent the default minify from processing and causing edge cases
* Compatibility: Add lazy load compatibility with Elementor image widget and sections/column backgrounds and overlays
* Compatibility: Add lazy load compatibility with Elementor Pro posst grid cards and slick slider
* Compatibility: Add lazy load support for Google Maps widgets and combine Google Maps Pro module

### 3.0.16 ###

* Bug: Fix lazy load CSS to not be position absolute
* Bug: Fix edge case bug with DOMDocument mangling HTML entities
* Compatibility: Google Plus shut down, so support removed

### 3.0.15 ###

* Bug: Fix using css minify instead of JS minify

### 3.0.14 ###

* Bug: Set url scheme to prevent no protocol urls from bugging output
* Bug: Use prevAll and use find over children
* Bug: Don't process if url is empty
* Enhancement: Ensure avatar images are processed though CDN
* Enhancement: Add autoplay support for vimeo
* Compatibility: Add lazy load compatibility with "Recaptcha In WP Comments Form" plugin
* Compatibility: Add generic recaptcha lazy load support
* Compatibility: Add integration with Listify for lazyload compatibility
* Compatibility: Add integration with Buttonizer for lazy load compatibility
* Compatibility: Add integration with Masterslider for lazylopad compatibility
* Compatibility: Change file purge filter for compatibility with wp-rocket 3.2
* Misc: Update readme with more clear disclaimer

### 3.0.13 ###

* Bug: Bug fix fusion framework integration with opengraph and cdn causing crash
* Compatibility: Force override html minification outside admin to ensure it does not process before plugin html minification runs. This is prep for a sister CSS plugin update

### 3.0.12 ###

* Compatibility: Add support for use proof to be optimized
* Compatibility: Add support for convert kit to be optimized
* Compatibility: Add compatibility with thrive theme framework to ensure the logo's are processed for CDN replacements
* Compatibility: Add compatibility with thrive leads to ensure that shortcodes get processed for CDN replacements
* Compatibility: Add elementor ultimate addons compatibility for the gallery element and before/after slider
* Compatibility: Add elementor compatibility for the tab element
* Compatibility: Add CDN/device icon compatibility with avada/fusion framework

### 3.0.11 ###

* Bug: Inline tag encoding processed empty tags and outputted garbled data that caused JS errors. Empty tags are no longer encoded

### 3.0.10 ###

* Bug: Always encrypt scripts if there is content to prevent processing edge cases
* Bug: Remove found script in rewrite modules and inject remaining code as a new script to get re-processed to ensure nothing gets silently deleted
* Bug: Handle case where CDN domain may be just a domain and not a url
* Integration: Add CookieBot rewrite module
* Integration: Add CallRail rewrite module to skip processing
* Enhancement: Prevent any mediaelement embeds from auto starting for lazy loads
* Compatibility: Add integration with fusion framework to handle the privacy feature for lazy load compatibility
* Compatibility: Put autoplay in allow attribute due to chrome video changes
* Compatibility: Add compatibility with wp-rocket 3.1 due to JS minify class change


### 3.0.9 ###

* Bug: jQuery wrap appears to set the style attribute and not with and height so work around it
* Bug: Use maybe_unserialize in revolution slider integration module for forcing javascript options
* Bug: Set iframes with lazyloaded-video class to max width of 100% to prevent overflowing in video
* Feature: Add function to enable using a class video-size-linked-to-VIDEOID on a video iframe to force it to use the size of another video via jQuery in edge cases where the image sizes don't match
* Enhancement: Disable a3 lazy load if enabled but we are logged in and not caching logged in users, but allow filter `rocket_footer_js_lazy_load_members_override` to override
* Enhancement: Add css class lazyloaded-video to processed videos to be styled
* Compatibility: Add compatibility with theme fusion avada/fusion builder
* Compatibility: Add compatibility with fusion builder/visual composer combination to convert css class to data-attribute  for video size linking
* Compatibility: Add further CSS compatibility with Visual Composer
* Compatibility: Re-render fusion carousel when any of its images are lazy loaded
* Compatibility: Add compatibility CSS with visual composer to override margins

### 3.0.8 ###

* Don't use PHP_INT_MAX on rocket_buffer

### 3.0.7 ###

* Bug: Don't process background images if lazy load is not enabled
* Bug: Add workaround to force divi parallax's to re-render on lazyload
 as well as the default all
* Integration: Add genesis framework integration
* Enhancement: Ensure get_rocket_cdn_url uses all css/js zones
* Enhancement: Better handling of video lazy load placeholder size and support p tags
* Compatibility: Add rewrite module to Prevent Stripe.js from being minified as they don't allow it
* Compatibility: Add magiczoom compatibility with lazy load
* Compatibility: Add MemberPress integration to force bundled zxcvbn script to be CDN'ified
* Compatibility: Add compatibility with AddThis script to exclude script since it doesn't function minified

 ### 3.0.6 ###

* Enable autoplay on lazy load videos
* Add loading spinner CSS for lazy load videos

### 3.0.5 ###

* Add fb-like-box class to list of facebook widgets to lazyload
* Don't check libxml version on body fix
* Exclude Shareaholic JS from minify

### 3.0.4 ###

* Update framework

### 3.0.3 ###

* Remove accidental global font override
* Remove bad lazy loading default that could interfere with minify

### 3.0.2 ###

* Fix Bugs in Video lazy loading
* Prevent crash from undefined is_plugin_active in some situations
* Skip lazy load iframe if is in a noscript

### 3.0.1 ###

* Fix Bug in Google Plus lazy loading


### 3.0.0 ###

This is considered  a ***MAJOR*** release due to the amount of effort that has been invested since the last release in 2017

* BUGS!: Too many bug fixes to give out in detail. It would be ideal to review git commits in this case
* Feature: Add lazyloading for CSS background images
* Feature: Add lazy load for google full page ads
* Feature: Add Big Bat support
* Feature: Add rewrite support for Klaviyo Analytics
* Feature: Add rewrite support for Youtube embed/iframe API
* Integration: Add integration with Wonder Plugin Carousel
* Integration: Add integration with Smart Slider 3
* Integration: Add integration with Qocode Theme Framework
* Integration: Add integration with MetaSlider
* Integration: Add integration with Google Maps Widget Pro
* Integration: Add integration with Divi Popup Builder
* Integration: Add integration with Bridge Theme
* Integration: Add Audio integration to properly handle HTML 5 audio
* Integration: Add a3 lazy load integration to ensure CDN is used for all content
* Integration: Add integration with Gravity Forms
* Integration: Add Lazyload support for Qode Framework google maps
* Integration: Add integration with Divi Builder
* Integration: Add integration with BNE flyout
* Integration: Add integration with WPEX theme framework
* Integration: Add integration with PressCore Theme framework
* Enhancement/Bug: Bundle a patched and updated a3 lazy Load lazyload XT library version
* Enhancement: Automatically download the most high resolution youtube thumbnail
* Enhancement: Ensure lazy load supports iframes with new script
* Compatibility: Add compatibility with Visual Composer
* Compatibility: Add revslider lazy load compatibility
* Compatibility: Add woocommerce integration to disable many cache hooks to reduce problems
* Compatibility: Add Divi LazyLoad compatibility
* Compatibility: Disable lazy load if divi frontend pagebuilder is running
* Compatibility: Add workaround technique for processing inline javascript that has html
* Deprecated: Remove google tag manager minify since it causes problems

### 2.0.0 ###

This is a ***MAJOR*** release and over 50% of the code is rewritten. While it has been extensively tested, there may still be bugs! Please test in a development site before deploying! Due to the amount of work, only a summary of this version will be detailed below.

* ***Major*** rewrite using new composer based framework.
* Feature: Add McAfee Secure integration
* Feature: Add Revolution Slider integration
* Feature: Add video embed lazyload and download thumnails locally
* Feature: Add Pin Interest lazyLoad
* Feature: Add Blog Her Ads lazyload
* Feature: Hijack JS document.write to enable 3rd party scripts to inject html safely
* Feature: Add Hub Spot rewriting
* Feature: Add Pindom Prum rewrite

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

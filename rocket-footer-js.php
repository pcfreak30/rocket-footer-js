<?php

/**
 * Plugin Name:       WP Rocket Footer JS
 * Plugin URI:       https://github.com/pcfreak30/rocket-footer-js
 * Description:       Unofficial WP-Rocket addon to force all JS both external and inline to the footer
 * Version:           3.2.3
 * Author:            Derrick Hammer
 * Author URI:        https://www.derrickhammer.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rocket-footer-js
 */

use Dice\Dice;


/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @return \Rocket\Footer\JS
 */
function rocket_footer_js() {
	return rocket_footer_js_container()->create( '\\Rocket\\Footer\\JS' );
}

/**
 * @param string $env
 *
 * @return \Dice\Dice
 */
function rocket_footer_js_container( $env = 'prod' ) {
	static $container;
	if ( empty( $container ) ) {
		$container = new Dice();
		include __DIR__ . "/config_{$env}.php";
	}

	return $container;
}

/**
 * Init function shortcut
 */
function rocket_footer_js_init() {
	rocket_footer_js()->init();
}

/**
 * Activate function shortcut
 */
function rocket_footer_js_activate() {
	rocket_footer_js()->init();
	rocket_footer_js()->activate();
}

/**
 * Deactivate function shortcut
 */
function rocket_footer_js_deactivate() {
	rocket_footer_js()->deactivate();
}

/**
 * Error for older php
 */
function rocket_footer_js_php_upgrade_notice() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s requires a minimum PHP version of 5.4.0. Your current version is: %s. Please contact your host to upgrade.</p>
	</div>', $info['Name'], PHP_VERSION
		)
	);
}

/**
 * Error if vendors autoload is missing
 */
function rocket_footer_js_php_vendor_missing() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s is corrupted it seems, please re-install the plugin.</p>
	</div>', $info['Name']
		)
	);
}

if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
	add_action( 'admin_notices', 'rocket_footer_js_php_upgrade_notice' );
} else {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		include_once __DIR__ . '/vendor/autoload.php';
		add_action( 'plugins_loaded', 'rocket_footer_js_init', 11 );
		register_activation_hook( __FILE__, 'rocket_footer_js_activate' );
		register_deactivation_hook( __FILE__, 'rocket_footer_js_deactivate' );
	} else {
		add_action( 'admin_notices', 'rocket_footer_js_php_vendor_missing' );
	}
}

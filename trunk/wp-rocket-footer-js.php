<?php
/**
 * Plugin Name:       WP-Rocket Footer JS
 * Plugin URI:       https://github.com/pcfreak30/wp-rocket-footer-js
 * Description:       Force all JS both external and inline to the footer
 * Version:           1.0.0
 * Author:            Derrick Hammer
 * Author URI:        https://www.derrickhammer.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-rocket-footer-js
 */
/*
 * Misc function to return one since wordpress doesn't have __return_one
 * @since 1.0.0
 */
function _rocket_return_one() {
	return 1;
}

/**
 * Finds all inline scripts and puts them right before the closing body tag in the order found
 *
 * @since 1.0.0
 *
 * @param $buffer
 *
 * @return mixed
 */
function rocket_footer_js_inline( $buffer ) {
	// Only run if JS minify is on
	if ( get_rocket_option( 'minify_js' ) ) {
		// Import HTML
		$document = new DOMDocument();
		if ( ! @$document->loadHTML( $buffer ) ) {
			return $buffer;
		}
		/** @var array $tags_match */
		/** @var DOMNode $body */
		// Get body tag
		$body                   = $document->getElementsByTagName( 'body' )->item( 0 );
		$tags                   = array();
		$variable_tags          = array();
		$external_tags          = array();
		$enqueued_variable_tags = array();
		// Get all localized scripts
		foreach ( array_unique( wp_scripts()->queue ) as $item ) {
			$data = wp_scripts()->print_extra_script( $item, false );
			if ( ! empty( $data ) ) {
				$enqueued_variable_tags[] = '/* <![CDATA[ */' . $data . '/* ]]> */';
			}
		}
		// Get array list of script DOMElement's. We must build arrays since modifying in-loop does mucky things to the collection and causes items to get lost/skipped.
		foreach ( $document->getElementsByTagName( 'script' ) as $tag ) {
			/** @var DOMElement $tag */
			$src = $tag->getAttribute( 'src' );

			if ( ! empty( $src ) ) {
				$external_tags[] = $tag;
				continue;
			}
			if ( in_array( str_replace( "\n", '', $tag->textContent ), $enqueued_variable_tags ) ) {
				$variable_tags[] = $tag;
			} else {
				$tags[] = $tag;
			}
		}
		// Get inline minify setting and load JSMin if needed
		$minify_inline_js = get_rocket_option( 'minify_html_inline_js', false );
		if ( ! class_exists( 'JSMin' ) && $minify_inline_js ) {
			require( WP_ROCKET_PATH . 'min/lib/JSMin.php' );
		}
		$js = '';
		// We have external scripts
		if ( $external_tags ) {
			// Build up javascript and remove nodes
			foreach ( $variable_tags as $tag ) {
				$js .= ';' . $tag->textContent;
				$tag->parentNode->removeChild( $tag );
			}
			// Minify?
			if ( $minify_inline_js ) {
				$js = rocket_minify_inline_js( $js );
			}
			// Create script element
			$main_variable_tag = $document->createElement( 'script', $js );
			$main_variable_tag->setAttribute( 'type', 'text/javascript' );
			// Add element to footer
			$body->appendChild( $main_variable_tag );
			$js = '';
		} else {
			// Combine back with other tags
			$tags = array_merge( $variable_tags, $tags );
		}
		// Move all external tags to footer
		foreach ( $external_tags as $tag ) {
			$tag->parentNode->removeChild( $tag );
			$body->appendChild( $tag );
		}
		//Combine all inline tags to one
		foreach ( $tags as $tag ) {
			$js .= ';' . $tag->textContent;
			$tag->parentNode->removeChild( $tag );
		}
		// Minify?
		if ( $minify_inline_js ) {
			$js = rocket_minify_inline_js( $js );
		}
		//Create script tag
		$main_tag = $document->createElement( 'script', $js );
		$main_tag->setAttribute( 'type', 'text/javascript' );
		// Add element to footer
		$body->appendChild( $main_tag );
		//Get HTML
		$buffer = $document->saveHTML();
		// If HTML minify is on, process it
		if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
			$buffer = rocket_minify_html( $buffer );
		}
	}

	return $buffer;
}

/**
 * Processes all enqueued scripts and forces them to the footer
 *
 */
function rocket_force_js_footer() {
	/** @var WP_Scripts $wp_scripts */
	$wp_scripts = wp_scripts();
	if ( ! is_admin() ) {
		foreach ( $wp_scripts->registered as $script ) {
			if ( 1 !== $wp_scripts->get_data( $script->handle, 'group' ) ) {
				$wp_scripts->add_data( $script->handle, 'group', 1 );
				if ( ! empty( $script->src ) ) {
					$wp_scripts->in_footer = array_unique( array_merge( $wp_scripts->in_footer, (array) $script->handle ) );
				}
			} else if ( ! in_array( $script->handle, $wp_scripts->in_footer ) && ! empty( $script->src ) ) {
				$wp_scripts->in_footer[] = $script->handle;
			}
			if ( wp_script_is( $script->handle, 'enqueued' ) ) {
				foreach ( $script->deps as $dep ) {
					$wp_scripts->queue = array_unique( array_merge( $wp_scripts->registered[ $dep ]->deps, (array) $wp_scripts->queue ) );
				}

			}
		}
	}
}

/*
 * This is a workaround to remove dummy script tags for script aliases
 * */
function rocket_remove_empty_footer_js() {
	global $rocket_enqueue_js_in_footer;
	$items = array();
	foreach ( wp_scripts()->done as $item ) {
		if ( ! empty( $rocket_enqueue_js_in_footer[ $item ] ) ) {
			$items[ $item ] = $rocket_enqueue_js_in_footer[ $item ];
		}
	}
	$rocket_enqueue_js_in_footer = $items;
}


/**
 * Throw error if WP-Rocket Doesn't exist, but auto-activate it if it does and not enabled
 */
function rocket_footer_js_activate() {
	if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
		activate_plugins( 'wp-rocket/wp-rocket.php' );
	}
}

/**
 * Deactivate and show error if WP-Rocket is missing
 */
function rocket_footer_js_plugins_loaded() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$error = false;
	if ( validate_plugin( 'wp-rocket/wp-rocket.php' ) ) {
		$error = true;
		add_action( 'admin_notices', 'rocket_footer_js_activate_error_no_wprocket' );
	} else if ( ! class_exists( 'DOMDocument' ) ) {
		$error = true;
		add_action( 'admin_notices', 'rocket_footer_js_activate_error_no_domdocument' );
	}
	if ( $error ) {
		deactivate_plugins( basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . basename( __FILE__ ) );
	}
}

/**
 * Error function if WP Rocket is missing
 */
function rocket_footer_js_activate_error_no_wprocket() {
	$info = get_plugin_data( __FILE__ );
	_e( sprintf( '
	<div class="error notice">
		<p>Opps! %s requires WP-Rocket! Please Download at <a href="http://www.wp-rocket.me">www.wp-rocket.me</a></p>
	</div>', $info['Name'] ) );
}

/**
 * Error function if PHP XML is not enabled
 */
function rocket_footer_js_activate_error_no_domdocument() {
	$info = get_plugin_data( __FILE__ );
	_e( sprintf( '
	<div class="error notice">
		<p>Opps! %s requires PHP XML extension! Please contact your web host or system administrator to get this installed.</p>
	</div>', $info['Name'] ) );
}

/**
 * Check if disable emoji is on, and if not, move emoji to footer
 */
function rocket_footer_js_init() {
	if ( function_exists( 'get_rocket_option' ) && ! get_rocket_option( 'emoji', 0 ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		add_action( 'wp_footer', 'print_emoji_detection_script' );
	}
}

add_action( 'wp_print_scripts', 'rocket_force_js_footer' );
add_action( 'wp_footer', 'rocket_remove_empty_footer_js', 21 );
add_action( 'plugins_loaded', 'rocket_footer_js_plugins_loaded' );
add_action( 'init', 'rocket_footer_js_init' );
add_filter( 'rocket_buffer', 'rocket_footer_js_inline', PHP_INT_MAX );
add_filter( 'pre_get_rocket_option_minify_js_combine_all', '_rocket_return_one' );


register_activation_hook( __FILE__, 'rocket_footer_js_activate' );
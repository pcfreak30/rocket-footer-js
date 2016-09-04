<?php
/**
 * Plugin Name:       WP-Rocket Footer JS
 * Plugin URI:       https://github.com/pcfreak30/rocket-footer-js
 * Description:       Unofficial WP-Rocket addon to force all JS both external and inline to the footer
 * Version:           1.1.10
 * Author:            Derrick Hammer
 * Author URI:        https://www.derrickhammer.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rocket-footer-js
 */
/**
 * Main function to combine all inline scripts and external into one file. Excludes "localized" scripts.
 *
 * @since 1.0.0
 *
 * @param $buffer
 *
 * @return mixed
 */
function rocket_footer_js_inline( $buffer ) {
	//Get debug status
	$display_errors = ini_get( 'display_errors' );
	$display_errors = ! empty( $display_errors ) && 'off' !== $display_errors;
	$debug          = ( defined( 'WP_DEBUG' ) && WP_DEBUG || $display_errors );
	//Remove filter to override JS minify option
	remove_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
	// Only run if JS minify is on
	if ( get_rocket_option( 'minify_js' ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
		// Import HTML
		$document = new DOMDocument();
		if ( ! @$document->loadHTML( $buffer ) ) {
			return $buffer;
		}
		/** @var array $tags_match */
		/** @var DOMNode $body */
		// Get body tag
		$body                   = $document->getElementsByTagName( 'body' )->item( 0 );
		$tags = [];
		$urls = [];
		$variable_tags = [];
		$enqueued_variable_tags = [];
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
			if ( '1' == $tag->getAttribute( 'data-no-minify' || in_array( $tag->getAttribute( 'type' ), [
						'x-tmpl-mustache',
						'text/x-handlebars-template',
						'text/template',
					] ) )
			) {
				continue;
			}
			if ( in_array( str_replace( "\n", '', $tag->textContent ), $enqueued_variable_tags ) ) {
				$variable_tags[] = $tag;
			} else {
				// Skip ld+json and leave it in the header
				if ( 'application/ld+json' != $tag->getAttribute( 'type' ) ) {
					$tags[] = $tag;
				}
			}
		}
		// Get inline minify setting and load JSMin if needed
		$minify_inline_js = get_rocket_option( 'minify_html_inline_js', false );
		if ( ! class_exists( 'JSMin' ) && $minify_inline_js ) {
			require( WP_ROCKET_PATH . 'min/lib/JSMin.php' );
		}
		$js = '';
		//Get home URL
		$home = home_url();
		// Get our domain
		$domain = parse_url( $home, PHP_URL_HOST );
		// Remote fetch external scripts
		$cdn_domains = get_rocket_cdn_cnames();
		// Get the hostname for each CDN CNAME
		foreach ( $cdn_domains as &$cdn_domain ) {
			$cdn_domain_parts = parse_url( $cdn_domain );
			$cdn_domain       = $cdn_domain_parts['host'];
		}
		// Cleanup
		unset( $cdn_domain_parts, $cdn_domain );
		// Get our cache path
		$cache_path = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/';
		// If we have a user logged in, include user ID in filename to be unique as we may have user only JS content. Otherwise file will be a hash of (minify-global-UNIQUEID).js
		if ( is_user_logged_in() ) {
			$filename = $cache_path . md5( 'minify-' . get_current_user_id() . '-' . create_rocket_uniqid() ) . '.js';
		} else {
			$filename = $cache_path . md5( 'minify-global' . create_rocket_uniqid() ) . '.js';
		}
		// Create cache dir if needed
		if ( ! is_dir( $cache_path ) ) {
			rocket_mkdir_p( $cache_path );
		}
		/** @var DOMElement $tag */
		// Remove all elements from DOM
		foreach ( array_merge( $variable_tags, $tags ) as $tag ) {
			$tag->parentNode->removeChild( $tag );
		}
		// lets process them scripts!
		foreach ( $tags as $index => $tag ) {
			// Remove from array by default
			$remove = true;
			$src    = $tag->getAttribute( 'src' );
			// If the last character is not a semicolon, and we have content,add one to prevent syntax errors
			if ( ';' != substr( $js, - 1, 1 ) && strlen( $js ) > 0 ) {
				$js .= ';';
			}
			//Decode html entities
			$src = html_entity_decode( preg_replace( '/((?<!&)#.*;)/', '&$1', $src ) );
			// We have a external script?
			if ( ! empty( $src ) ) {
				//Handle no protocol urls
				$src = rocket_add_url_protocol( $src );
				//Has it been processed before?
				if ( ! in_array( $src, $urls ) ) {
					// Get host of tag source
					$src_host = parse_url( $src, PHP_URL_HOST );
					// Being remote is defined as not having our home url and not being in the CDN list. However if the file does not have a JS extension, assume its a dynamic script generating JS, so we need to web fetch it.
					if ( ( $src_host != $domain && ! in_array( $src_host, $cdn_domains ) ) || 'js' != pathinfo( parse_url( $src, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) {
						$file = wp_remote_get( $src, [
							'user-agent' => 'WP-Rocket',
							'sslverify'  => false,
						] );
						// Catch Error
						if ( $file instanceof \WP_Error || ( is_array( $file ) && ( empty( $file['response']['code'] ) || ! in_array( $file['response']['code'], [
										200,
										304,
									] ) ) )
						) {
							// Only log if debug mode is on
							if ( $debug ) {
								error_log( 'URL: ' . $src . ' Status:' . ( $file instanceof \WP_Error ? 'N/A' : $file['code'] ) . ' Error:' . ( $file instanceof \WP_Error ? $file->get_error_message() : 'N/A' ) );
							}
						} else {
							$js .= $debug ? $file['body'] : rocket_minify_inline_js( $file['body'] );
						}
					} else {
						// Remove query strings
						$src_file = $src;
						if ( false !== strpos( $src, '?' ) ) {
							$src_file = substr( $src, 0, strpos( $src, strrchr( $src, '?' ) ) );
						}
						// Break up url
						$url_parts         = parse_url( $src_file );
						$url_parts['host'] = $domain;
						/*
						 * Check and see what version of php-http we have.
						 * 1.x uses procedural functions.
						 * 2.x uses OOP classes with a http namespace.
						 * Convert the address to a path, minify, and add to buffer.
						 */
						if ( class_exists( 'http\Url' ) ) {
							$url     = new \http\Url( $url_parts );
							$url     = $url->toString();
							$js_part = rocket_footer_get_content( rocket_footer_get_content( str_replace( $home, ABSPATH, $src_file ) ) );
						} else {
							if ( ! function_exists( 'http_build_url' ) ) {
								require __DIR__ . '/http_build_url.php';
							}
							$js_part = rocket_footer_get_content( str_replace( $home, ABSPATH, http_build_url( $url_parts ) ) );
						}
						$js_part = $debug ? $js_part : rocket_minify_inline_js( $js_part );
						$js .= $js_part;
					}
					//Debug log URL
					if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || $display_errors ) {
						error_log( 'Processed URL: ' . $src );
					}
					//Add to array so we don't process again
					$urls[] = $src;
				}

			} else {
				// Remove any conditional comments for IE that somehow was put in the script tag
				$js_part = preg_replace( '/(?:<!--)?\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '', $tag->textContent );
				//Minify ?
				if ( $minify_inline_js ) {
					$js_part = $debug ? $js_part : rocket_minify_inline_js( $js_part );
				}
				//Add inline JS to buffer
				$js .= $js_part;
			}
			// For later, if we dont want the tag removed so it get processed below
			if ( $remove ) {
				unset( $tags[ $index ] );
			}
		}
		if ( $debug ) {
			error_log( 'Processed URL list: ' . var_export( $urls, true ) );
		}
		$inline_js = '';
		//Combine all inline tags to one
		foreach ( array_merge( $variable_tags, $tags ) as $tag ) {
			// If the last character is not a semicolon, and we have content,add one to prevent syntax errors
			if ( ';' != substr( $inline_js, - 1, 1 ) && strlen( $inline_js ) > 0 ) {
				$js .= ';';
			}
			$inline_js .= $tag->textContent;
		}
		if ( ! empty( $inline_js ) ) {
			//Create script tag
			$inline_tag = $document->createElement( 'script', $inline_js );
			$inline_tag->setAttribute( 'type', 'text/javascript' );
			// Add element to footer
			$body->appendChild( $inline_tag );
		}
		rocket_put_content( $filename, $js );
		$src = get_rocket_cdn_url( set_url_scheme( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $filename ) ) );
		// Create script element
		$external_tag = $document->createElement( 'script' );
		$external_tag->setAttribute( 'type', 'text/javascript' );
		$external_tag->setAttribute( 'src', $src );
		$external_tag->setAttribute( 'data-minify', '1' );
		$external_tag->setAttribute( 'async', false );
		// Add element to footer
		$body->appendChild( $external_tag );
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
 * @since 1.0.0
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
 * This is a workaround to remove dummy script tags for script aliases. Pending core bug report on dependencies
 *
 * @since 1.0.0
 *
 * */
function rocket_remove_empty_footer_js() {
	global $rocket_enqueue_js_in_footer;
	$items = [];
	foreach ( wp_scripts()->done as $item ) {
		if ( ! empty( $rocket_enqueue_js_in_footer[ $item ] ) ) {
			$items[ $item ] = $rocket_enqueue_js_in_footer[ $item ];
		}
	}
	$rocket_enqueue_js_in_footer = $items;
}


/**
 * Throw error if WP-Rocket Doesn't exist, but auto-activate it if it does and not enabled
 *
 * @since 1.0.0
 *
 */
function rocket_footer_js_activate() {
	if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
		activate_plugins( 'wp-rocket/wp-rocket.php' );
	}
}

/**
 * Deactivate and show error if WP-Rocket is missing
 *
 * @since 1.0.0
 *
 */
function rocket_footer_js_plugins_loaded() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$error = false;
	if ( validate_plugin( 'wp-rocket/wp-rocket.php' ) ) {
		$error = true;
		add_action( 'admin_notices', 'rocket_footer_js_activate_error_no_wprocket' );
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		$error = true;
		add_action( 'admin_notices', 'rocket_footer_js_activate_error_no_domdocument' );
	}
	if ( $error ) {
		deactivate_plugins( basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . basename( __FILE__ ) );
	}
}

/**
 * Error function if WP Rocket is missing
 *
 * @since 1.0.0
 *
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
 *
 * @since 1.0.0
 *
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
 *
 * @since 1.0.0
 *
 */
function rocket_footer_js_init() {
	if ( function_exists( 'get_rocket_option' ) && ! get_rocket_option( 'emoji', 0 ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		add_action( 'wp_footer', 'print_emoji_detection_script' );
	}
}

/**
 * @param $file
 *
 * @since 1.0.0
 *
 * @return bool|string
 */
function rocket_footer_get_content( $file ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
	$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

	return $direct_filesystem->get_contents( $file );
}

/*
 * wp_print_scripts and wp_footer hooks can be used to force enqueue JS in the footer, but may not be compatible with bad plugins that don't register their JS properly. Will remain here for the time that this may improve. DOMDocument parsing will be used until then.
 */
//add_action( 'wp_print_scripts', 'rocket_force_js_footer' );
//add_action( 'wp_footer', 'rocket_remove_empty_footer_js', 21 );
add_action( 'plugins_loaded', 'rocket_footer_js_plugins_loaded' );
add_action( 'init', 'rocket_footer_js_init' );
add_filter( 'rocket_buffer', 'rocket_footer_js_inline', PHP_INT_MAX );
add_filter( 'pre_get_rocket_option_minify_js_combine_all', '__return_zero' );
//Only override JS Minify option of front end
if ( ! is_admin() ) {
	add_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
}

register_activation_hook( __FILE__, 'rocket_footer_js_activate' );
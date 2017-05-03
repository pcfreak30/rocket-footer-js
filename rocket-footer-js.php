<?php
/**
 * Plugin Name:       WP Rocket Footer JS
 * Plugin URI:       https://github.com/pcfreak30/rocket-footer-js
 * Description:       Unofficial WP-Rocket addon to force all JS both external and inline to the footer
 * Version:           1.4.6
 * Author:            Derrick Hammer
 * Author URI:        https://www.derrickhammer.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rocket-footer-js
 */

define( 'ROCKET_FOOTER_JS_TRANSIENT_PREFIX', 'rocket_footer_js_' );


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
	$debug = rocket_footer_js_debug_enabled();
	//Remove filter to override JS & HTML minify option
	remove_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
	remove_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
	// Only run if JS minify is on
	if ( get_rocket_option( 'minify_js' ) && ( ! defined( 'DONOTMINIFYJS' ) || ! DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
		// Import HTML
		$document = new DOMDocument();
		if ( ! @$document->loadHTML( mb_convert_encoding( $buffer, 'HTML-ENTITIES', 'UTF-8' ) ) ) {
			return $buffer;
		}
		rocket_footer_js_rewrite_js_loaders( $document );
		/** @var array $tags_match */
		/** @var DOMNode $body */
		// Get body tag
		$body                   = $document->getElementsByTagName( 'body' )->item( 0 );
		$tags                   = array();
		$urls                   = array();
		$cache_list             = array();
		$variable_tags          = array();
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
			if ( '1' == $tag->getAttribute( 'data-no-minify' ) || in_array( $tag->getAttribute( 'type' ), array(
					'x-tmpl-mustache',
					'text/x-handlebars-template',
					'text/template',
					'text/html',
				) )
			) {
				continue;
			}
			if ( in_array( str_replace( "\n", '', $tag->textContent ), $enqueued_variable_tags ) ) {
				$variable_tags[] = $tag;
			} else {
				// Skip ld+json and leave it in the header
				if ( 'application/ld+json' != $tag->getAttribute( 'type' ) ) {
					$tags[] = $tag;
					$src    = $tag->getAttribute( 'src' );
					if ( ! empty( $src ) ) {
						$cache_list['external'][] = $src;
					} else if ( ! empty( $tag->textContent ) ) {
						$cache_list['inline'][] = $tag->textContent;
					}
				}
			}
		}
		//Check post cache
		$post_cache_id_hash = md5( serialize( $cache_list ) );
		$post_cache_id      = array();
		if ( is_singular() ) {
			$post_cache_id [] = 'post_' . get_the_ID();
		} else if ( is_tag() || is_category() || is_tax() ) {
			$post_cache_id [] = 'tax_' . get_queried_object()->term_id;
		} else if ( is_author() ) {
			$post_cache_id [] = 'author_' . get_the_author_meta( 'ID' );
		} else {
			$post_cache_id [] = 'generic';
		}
		$post_cache_id [] = $post_cache_id_hash;
		if ( is_user_logged_in() ) {
			$post_cache_id [] = wp_get_current_user()->roles[0];
		}
		$post_cache = rocket_footer_js__get_cache_fragment( $post_cache_id );
		if ( ! empty( $post_cache ) ) {
			// Cached file is gone, we dont have cache
			if ( ! file_exists( $post_cache['filename'] ) ) {
				$post_cache = false;
			}
		}
		// Get inline minify setting and load JSMin if needed
		$minify_inline_js = get_rocket_option( 'minify_html_inline_js', false );
		if ( ! class_exists( 'JSMin' ) && $minify_inline_js ) {
			require( WP_ROCKET_PATH . 'min/lib/JSMin.php' );
		}
		$js = '';
		//Get home URL
		$home = set_url_scheme( home_url() );
		// Get our domain
		$domain = parse_url( $home, PHP_URL_HOST );
		if ( empty( $post_cache ) ) {
			// Remote fetch external scripts
			$cdn_domains = get_rocket_cdn_cnames();
			// Get the hostname for each CDN CNAME
			foreach ( $cdn_domains as &$cdn_domain ) {
				$cdn_domain_parts = parse_url( $cdn_domain );
				$cdn_domain       = $cdn_domain_parts['host'];
			}
			// Cleanup
			unset( $cdn_domain_parts, $cdn_domain );
			// Get our post_cache path
			$cache_path = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/';
			// If we have a user logged in, include user ID in filename to be unique as we may have user only JS content. Otherwise file will be a hash of (minify-global-UNIQUEID).js
			if ( is_user_logged_in() ) {
				$filename = $cache_path . md5( 'minify-' . get_current_user_id() . '-' . create_rocket_uniqid() ) . '.js';
			} else {
				$filename = $cache_path . md5( 'minify-global' . create_rocket_uniqid() ) . '.js';
			}
			// Create post_cache dir if needed
			if ( ! is_dir( $cache_path ) ) {
				rocket_mkdir_p( $cache_path );
			}
		}
		/** @var DOMElement $tag */
		// Remove all elements from DOM
		foreach ( array_merge( $variable_tags, $tags ) as $tag ) {
			$tag->parentNode->removeChild( $tag );
		}

		// lets process them scripts!
		$tags_ref = &$tags;
		foreach ( $tags_ref as $index => $tag ) {
			// Remove from array by default
			$remove = true;
			$src    = $tag->getAttribute( 'src' );
			// If the last character is not a semicolon, and we have content,add one to prevent syntax errors
			if ( ! in_array( substr( $js, - 1, 1 ), array( ';', "\n" ) ) && strlen( $js ) > 0 ) {
				$js .= ";\n";
			}
			//Decode html entities
			$src = html_entity_decode( preg_replace( '/((?<!&)#.*;)/', '&$1', $src ) );
			// We have a external script?
			if ( ! empty( $src ) ) {
				// Only run if there is no post cache
				if ( empty( $post_cache ) ) {
					if ( 0 === strpos( $src, '//' ) ) {
						//Handle no protocol urls
						$src = rocket_add_url_protocol( $src );
					}
					//Has it been processed before?
					if ( ! in_array( $src, $urls ) ) {
						// Get host of tag source
						$src_host = parse_url( $src, PHP_URL_HOST );
						// Being remote is defined as not having our home url and not being in the CDN list. However if the file does not have a JS extension, assume its a dynamic script generating JS, so we need to web fetch it.
						if ( 0 != strpos( $src, '/' ) && ( ( $src_host != $domain && ! in_array( $src_host, $cdn_domains ) ) || 'js' != pathinfo( parse_url( $src, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ) {
							// Check item cache
							$item_cache_id = array( md5( $src ) );
							$item_cache    = rocket_footer_js__get_cache_fragment( $item_cache_id );
							// Only run if there is no item cache
							if ( empty( $item_cache ) ) {
								$file = rocket_footer_js_remote_fetch( $src );
								// Catch Error
								if ( ! empty( $file ) ) {
									$js_part_cache = rocket_footer_js_process_remote_script( $src, $file, $document, $tags );
									$js_part       = $debug ? $js_part_cache : rocket_footer_js_minify( $js_part_cache );;
									if ( $js_part_cache != $file ) {
										$js_part_cache = $file;
										$js_part_cache = $debug ? $js_part_cache : rocket_footer_js_minify( $js_part_cache );
									} else {
										$js_part_cache = $js_part;
									}
									rocket_footer_js_update_cache_fragment( $item_cache_id, $js_part_cache );
									$js .= $js_part;
								}
							} else {
								$js .= rocket_footer_js_process_remote_script( $src, $item_cache, $document, $tags );
							}
						} else {
							if ( 0 == strpos( $src, '/' ) ) {
								$src = $home . $src;
							}
							// Remove query strings
							$src_file = $src;
							if ( false !== strpos( $src, '?' ) ) {
								$src_file = substr( $src, 0, strpos( $src, strrchr( $src, '?' ) ) );
							}
							// Break up url
							$url_parts           = parse_url( $src_file );
							$url_parts['host']   = $domain;
							$url_parts['scheme'] = is_ssl() ? 'https' : 'http';
							/*
							 * Check and see what version of php-http we have.
							 * 1.x uses procedural functions.
							 * 2.x uses OOP classes with a http namespace.
							 * Convert the address to a path, minify, and add to buffer.
							 */
							if ( class_exists( 'http\Url' ) ) {
								$url = new \http\Url( $url_parts );
								$url = $url->toString();
							} else {
								if ( ! function_exists( 'http_build_url' ) ) {
									require __DIR__ . '/http_build_url.php';
								}
								$url = http_build_url( $url_parts );
							}


							// Check item cache
							$item_cache_id = array( md5( $src ) );
							$item_cache    = rocket_footer_js__get_cache_fragment( $item_cache_id );
							// Only run if there is no item cache
							if ( empty( $item_cache ) ) {
								$file          = rocket_footer_js_get_content( str_replace( $home, ABSPATH, $url ) );
								$js_part_cache = rocket_footer_js_process_local_script( $url, $file, $document, $tags_ref );
								$js_part       = $js_part_cache;

								$js_part = $debug ? $js_part : rocket_footer_js_minify( $js_part );
								if ( $js_part_cache != $file ) {
									$js_part_cache = $file;
									$js_part_cache = rocket_footer_js_minify( $js_part_cache );
								} else {
									$js_part_cache = $js_part;
								}
								if ( strpos( $js_part, 'sourceMappingURL' ) !== false ) {
									$js_part .= "\n";
								} else {
									$js_part = trim( $js_part );
								}
								$js .= $js_part;
								rocket_footer_js_update_cache_fragment( $item_cache_id, $js_part_cache );
							} else {
								$js .= rocket_footer_js_process_local_script( $url, $item_cache, $document, $tags_ref );
							}
						}
						//Debug log URL
						if ( rocket_footer_js_debug_enabled( true ) ) {
							error_log( 'Processed URL: ' . $src );
						}
						//Add to array so we don't process again
						$urls[] = $src;
					}
				}
			} else {
				// Check item cache
				$item_cache_id = array( md5( $tag->textContent ) );
				$item_cache    = rocket_footer_js__get_cache_fragment( $item_cache_id );
				// Only run if there is no item cache
				if ( empty( $item_cache ) ) {
					// Remove any conditional comments for IE that somehow was put in the script tag
					$js_part = preg_replace( '/(?:<!--)?\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '', $tag->textContent );
					//Minify ?
					if ( $minify_inline_js ) {
						$js_part = $debug ? $js_part : rocket_footer_js_minify( $js_part );
					}
					rocket_footer_js_update_cache_fragment( $item_cache_id, $js_part );
				} else {
					$js_part = $item_cache;
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
				$inline_js .= ';';
			}
			// Remove any conditional comments for IE that somehow was put in the script tag
			$inline_js .= preg_replace( '/(?:<!--)?\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '', $tag->textContent );
		}
		if ( ! empty( $inline_js ) ) {
			//Create script tag
			$inline_tag = $document->createElement( 'script', $inline_js );
			$inline_tag->setAttribute( 'type', 'text/javascript' );
			// Add element to footer
			$body->appendChild( $inline_tag );
		}
		if ( empty( $post_cache ) ) {
			rocket_put_content( $filename, $js );
			$src = get_rocket_cdn_url( set_url_scheme( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $filename ) ) );
			set_transient( $post_cache_id, compact( 'src', 'filename' ), get_rocket_purge_cron_interval() );
		} else {
			extract( $post_cache );
		}
		// Create script element
		$external_tag = $document->createElement( 'script' );
		$external_tag->setAttribute( 'type', 'text/javascript' );
		$external_tag->setAttribute( 'src', $src );
		$external_tag->setAttribute( 'data-minify', '1' );
		$external_tag->setAttribute( 'async', 'async' );
		// Add element to footer
		$body->appendChild( $external_tag );

		// Hack to fix a bug with libxml versions earlier than 2.9.x
		if ( 1 === version_compare( '2.9.0', LIBXML_DOTTED_VERSION ) ) {
			$body_class = $body->getAttribute( 'class' );
			if ( empty( $body_class ) ) {
				$body->setAttribute( 'class', implode( ' ', get_body_class() ) );
			}
		}


		//Get HTML
		$buffer = $document->saveHTML();

		// If HTML minify is on, process it
		if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
			$buffer = rocket_minify_html( $buffer );
			$buffer = preg_replace_callback( '~<WP_ROCKET_FOOTER_JS_LAZYLOAD_START\s*/>(.*)<WP_ROCKET_FOOTER_JS_LAZYLOAD_END\s*/>~s', '_rocket_footer_js_lazyload_html_callback', $buffer );
			$buffer = preg_replace_callback( '~<WP_ROCKET_FOOTER_JS_LAZYLOAD_START></WP_ROCKET_FOOTER_JS_LAZYLOAD_START>(.*)<WP_ROCKET_FOOTER_JS_LAZYLOAD_END></WP_ROCKET_FOOTER_JS_LAZYLOAD_END>~sU', '_rocket_footer_js_lazyload_html_callback', $buffer );
		}
	}

	return $buffer;
}

/**
 * @param $matches
 */
function _rocket_footer_js_lazyload_html_callback( $matches ) {
	return '<!-- ' . html_entity_decode( $matches[1] ) . ' -->';
}

/**
 * @param bool $or
 *
 * @return bool
 */
function rocket_footer_js_debug_enabled( $or = false ) {
	$display_errors = ini_get( 'display_errors' );
	$display_errors = ! empty( $display_errors ) && 'off' !== $display_errors;

	return $or ? ( ( defined( 'WP_DEBUG_LOG' ) || WP_DEBUG_LOG ) || $display_errors ) : ( ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) || $display_errors );

}

/**
 * Optimize common social media and analtics widgets with special lazy load support
 *
 * @since 1.2.4
 *
 * @param DOMDocument $document
 */
function rocket_footer_js_rewrite_js_loaders( &$document, &$content_document = null ) {
	$lazy_load                  = rocket_footer_js_lazy_load_enabled();
	$google_maps_instances      = array();
	$google_maps_tag            = null;
	$google_maps_script_id      = '';
	$google_maps_script_content = '';
	$google_adsense_count       = 0;
	$amazon_ads_count           = 0;
	$facebook_sdk_loaded        = false;
	static $vshare_counter = 0;

	if ( empty( $content_document ) ) {
		$content_document = $document;
	}

	$tags  = iterator_to_array( $document->getElementsByTagName( 'script' ) );
	$xpath = new DOMXPath( $content_document );

	foreach ( $tags as $index => $tag ) {
		/** @var DOMElement $tag */
		$src = $tag->getAttribute( 'src' );
		if ( 0 === strpos( $src, '//' ) ) {
			//Handle no protocol urls
			$src = rocket_add_url_protocol( $src );
		}
		if ( 'maps.googleapis.com' == parse_url( $src, PHP_URL_HOST ) ) {
			if ( preg_match( '/key=\w+/i', $src ) && empty( $google_maps_tag ) ) {
				$google_maps_tag = $tag;
			} else {
				$google_maps_instances[] = $tag;
			}
		}
	}
	if ( empty( $google_maps_tag ) && ! empty( $google_maps_instances ) ) {
		$google_maps_tag = array_shift( $google_maps_instances );
	}
	/** @var DOMElement $sub_tag */
	foreach ( $google_maps_instances as $sub_tag ) {
		$sub_tag->parentNode->removeChild( $sub_tag );
	}
	unset( $google_maps_instances );
	foreach ( $tags as $tag ) {
		/** @var DOMElement $tag */
		if ( '1' == $tag->getAttribute( 'data-no-minify' ) || in_array( $tag->getAttribute( 'type' ), array(
				'x-tmpl-mustache',
				'text/x-handlebars-template',
				'text/template',
			) )
		) {
			continue;
		}
		$src = $tag->getAttribute( 'src' );
		$src = rocket_add_url_protocol( $src );

		$content = str_replace( "\n", '', $tag->textContent );
		$content = str_replace( "\r", '', $content );
		$content = trim( $content, '/' );

		// Tawk.to
		if ( preg_match( '~var\s*Tawk_API\s*=\s*Tawk_API.*s1.src\s*=\s*\'(.*)\';.*s0\.parentNode\.insertBefore\(s1,s0\);\s*}\s*\)\(\);~sU', $content, $matches ) ) {
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', "{$matches[1]}" );
			$external_tag->setAttribute( 'async', false );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$tag->parentNode->removeChild( $tag );
		}
		// WP-Rocket LazyLoad
		if ( preg_match( '~\(function\(\s*w\s*,\s*d\s*\)\s*{\s*.*b\.src\s*=\s*"(.*)"\s*;.*\(\s*window,document\s*\)\s*;~s', $content, $matches ) ) {
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', "{$matches[1]}" );
			$external_tag->setAttribute( 'async', false );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$tag->parentNode->removeChild( $tag );
		}
		// Google Analytics
		if ( preg_match( '~\\(function\s*\(\s*i\s*,\s*s\s*,\s*o\s*,\s*g\s*,\s*r\s*,\s*a\s*,\s*m\s*\)\s*{\s*i\[\'GoogleAnalyticsObject\'\]\s*=\s*r;\s*i\[r\]\s*=\s*i\[r\]\s*\|\|\s*function\s*\(\)\s*\{.*\'(.*//(?:www\.)?google-analytics\.com/analytics\.js)\'\s*,\s*\'ga\'\s*\);~', $content, $matches ) || preg_match( '~\(function\s*\(\s*\) {\s*var\s*ga\s*=\s*document\s*\.\s*createElement.*\.google-analytics\.com/ga\.js.*\}\s*\)\s*\(\s*\);~', $content, $matches ) ) {
			preg_match_all( '~ga\s*\(\s*.*\s*\)\s*;~U', $content, $ga_calls );
			$ga_calls = call_user_func_array( 'array_merge', $ga_calls );
			if ( empty( $matches[1] ) ) {
				$matches[1] = ( is_ssl() ? 'https://ssl' : 'http://www' ) . '.google-analytics.com/ga.js';
			}
			$external_tag = $document->createElement( 'script', 'window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date; ' . implode( "\n", $ga_calls ) );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', "{$matches[1]}" );
			$external_tag->setAttribute( 'async', false );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$tag->parentNode->removeChild( $tag );
		}

		// Double Click Google Analytics
		if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{\s*var\s*ga\s*=\s*.*\s\'stats\.g\.doubleclick\.net/dc\.js\'.*s\s*.\s*parentNode\s*.\s*insertBefore\s*\(\s*ga\s*,\s*s\);\s*}\s*\)\s*\(\s*\);~', $content, $matches ) ) {
			preg_match_all( '~_gaq\s*\.\s*push\s*.*;~U', $content, $gaq_calls );
			$gaq_calls    = call_user_func_array( 'array_merge', $gaq_calls );
			$external_tag = $document->createElement( 'script', 'var _gaq = _gaq || [];' . implode( "\n", $gaq_calls ) );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', '//stats.g.doubleclick.net/dc.js' );
			$external_tag->setAttribute( 'async', false );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$tag->parentNode->removeChild( $tag );
		}
		// Facebook Pixel
		if ( preg_match( '~!?function\s*\(\s*f\s*,\s*b\s*,\s*e\s*,\s*v\s*,\s*n\s*,\s*t\s*,\s*s\s*\)\s*{\s*if\s*\(\s*f\s*\.\s*fbq\s*\)\s*return\s*;\s*n\s*=\s*f\s*.\s*fbq\s*=\s*function.*\s*\(\s*window\s*,\s*document\s*,\s*\'script\'\s*,\s*\'((?:https?:)?//connect.facebook.net/[\w_]+/fbevents.js)\'\s*\)\s*;~s', $content, $matches ) ) {
			preg_match_all( '~fbq\s*\(\s*(.*)\s*,\s*(.*)\s*\)\s*;~U', $content, $fbq_calls, PREG_SET_ORDER );
			foreach ( $fbq_calls as $index => $fbq_call ) {
				if ( ! empty( $fbq_call[1] ) && 'init' == trim( $fbq_call[1], "'" ) ) {
					$pixel_id = $fbq_call[2];
				}
				$fbq_calls[ $index ] = array( $fbq_call[0] );
			}
			if ( ! empty( $pixel_id ) ) {
				$fbq_calls    = call_user_func_array( 'array_merge', $fbq_calls );
				$external_tag = $document->createElement( 'script', '(function(a){a.fbq||(n=a.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)},a._fbq||(a._fbq=n));n.push=n;n.disableConfigLoading=!0;n.loaded=!0;n.version="2.0";n.queue=[]})(window);' . implode( "\n", $fbq_calls ) );
				$external_tag->setAttribute( 'type', 'text/javascript' );
				$tag->parentNode->insertBefore( $external_tag, $tag );
				$external_tag = $document->createElement( 'script' );
				$external_tag->setAttribute( 'type', 'text/javascript' );
				$external_tag->setAttribute( 'src', $matches[1] );
				$external_tag->setAttribute( 'async', false );
				$tag->parentNode->insertBefore( $external_tag, $tag );
				$external_tag = $document->createElement( 'script', 'fbq.registerPlugin("config:"+' . $pixel_id . ', {__fbEventsPlugin: 1,plugin: function(f, i){i.configLoaded(' . $pixel_id . ');}});' );
				$external_tag->setAttribute( 'type', 'text/javascript' );
				$tag->parentNode->insertBefore( $external_tag, $tag );

				$external_tag = $document->createElement( 'script' );
				$external_tag->setAttribute( 'type', 'text/javascript' );
				$external_tag->setAttribute( 'src', str_replace( 'fbevents.js', 'fbevents.plugins.identity.js', $matches[1] ) );
				$external_tag->setAttribute( 'async', false );
				$tag->parentNode->insertBefore( $external_tag, $tag );

				$content = str_replace( $matches[0], '', $content );
				foreach ( $fbq_calls as $fbq_call ) {
					$content = str_replace( $fbq_call, '', $content );
				}
				$content = trim( $content );
				if ( ! empty( $content ) ) {
					$external_tag = $document->createElement( 'script', $content );
					$external_tag->setAttribute( 'type', 'text/javascript' );
					$tag->parentNode->insertBefore( $external_tag, $tag );
				}
				$tag->parentNode->removeChild( $tag );
			}

		}

		// Google Web Fonts
		if ( preg_match( '~(WebFontConfig\s*=\s{.*};)?\s*\(\s*function\s*\(\s*\)\s*{\s*var\s*wf\s*=\s*document\s*\.\s*createElement\s*\(\s*\'script\'\s*\)\s*;.*s\s*.\s*parentNode\s*.insertBefore\s*\(\s*wf\s*,\s*s\)\s*;\s*}\s*\)\s*\(\s*\);~s', $content, $matches ) ) {
			$external_tag = $document->createElement( 'script', $matches[1] );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$tag->parentNode->insertBefore( $external_tag, $tag );

			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', rocket_add_url_protocol( '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js' ) );
			$tag->parentNode->insertBefore( $external_tag, $tag );

			$content = trim( str_replace( $matches[0], '', $content ) );
			if ( ! empty( $content ) ) {
				$external_tag = $document->createElement( 'script', $content );
				$external_tag->setAttribute( 'type', 'text/javascript' );
				$tag->parentNode->insertBefore( $external_tag, $tag );
			}

			$tag->parentNode->removeChild( $tag );
		}
		// Sumo Me
		if ( 'load.sumome.com' == parse_url( $src, PHP_URL_HOST ) && '' != $tag->getAttribute( 'data-sumo-site-id' ) ) {
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', $src );
			$tag->removeAttribute( 'src' );
			$tag->setAttribute( 'data-no-minify', '1' );
			$tag->parentNode->insertBefore( $external_tag, $tag );
		}

		// Avvo Tracking
		if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{\s*setTimeout\s*\(\s*function\s*\(\s*\){\s*var\s*s\s*=\s*.*s\s*\.\s*src="((?:https?:)?//ia\s*.avvo\s*.com/tracker/\w+.js)".*}\s*\)\s*\(\s*\)\s*;~s', $content, $matches ) ) {
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', "{$matches[1]}" );
			$external_tag->setAttribute( 'async', false );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$tag->parentNode->removeChild( $tag );
		}
		// Pushcrew Tracking
		if ( preg_match( '~\!?\(\s*function\s*\(\s*p\s*,\s*u\s*,\s*s\s*,\s*h\s*\)\{\s*.*\'((?:https?:)?//cdn\.pushcrew\.com/js/\w+\.js)\'.*\}\s*\)\s*\(\s*window\s*,\s*document\s*\);~s', $content, $matches ) ) {
			$external_tag = $document->createElement( 'script', "(function(p,u){p._pcq=p._pcq||[];p._pcq.push(['_currentTime',Date.now()]);})(window);" );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', "{$matches[1]}" );
			$external_tag->setAttribute( 'async', false );
			$tag->parentNode->insertBefore( $external_tag, $tag );
			$tag->parentNode->removeChild( $tag );
		}
		if ( $lazy_load ) {
			// Facebook
			if ( preg_match( '~\(\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*js\.src\s*=\s*"//connect\.facebook.net/[\w_]+/(?:sdk|all)\.js#(?:xfbml=\d|(?:version=[\w\.\d]+)|(?:&appId=\d*)&?)+"\s*;.*\s*\'facebook-jssdk\'\s*\)\);?~is', $content, $matches ) ) {
				if ( ! $facebook_sdk_loaded ) {
					$tag_content = str_replace( "\n", '', $document->saveHTML( $tag ) );
					$tag_content = str_replace( "\r", '', $tag_content );
					$tag_content = str_replace( array( '<script>//', '//</script>' ), array(
						'<script>',
						'</script>',
					), $tag_content );
					rocket_footer_js_lazyload_script( $tag_content, 'facebook-sdk', $tag, $document, $content_document );
					/** @var DOMElement $tag */
					foreach (
						array(
							'fb-page',
							'fb-like',
							'fb-quote',
							'fb-send',
							'fb-share-button',
							'fb-follow',
							'fb-video',
							'fb-post',
							'fb-comment-embed',
							'fb-comments',
						) as $class
					) {
						foreach ( $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]' ) as $tag ) {
							$tag->setAttribute( 'data-lazy-widget', 'facebook-sdk' );
						}
					}
					$facebook_sdk_loaded = true;
				} else {
					$tag->parentNode->removeChild( $tag );
				}

			}

			// Google Plus
			if ( false !== strpos( $src, 'apis.google.com/js/platform.js' ) ) {
				rocket_footer_js_lazyload_script( "<script type=\"text/javascript\">    (function() {      var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;      po.src = 'https://apis.google.com/js/platform.js';      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);    })();  </script>", 'google-plus-platform', $tag, $document, $content_document );
				/** @var DOMElement $tag */
				foreach (
					array(
						'//g:plusone',
						'//*[contains(concat(" ", normalize-space(@class), " "), " g-plusone ")]',
						'//*[contains(concat(" ", normalize-space(@class), " "), " g-plus ")]',
					) as $expression
				) {
					foreach ( $xpath->query( $expression ) as $tag ) {
						$tag->setAttribute( 'data-lazy-widget', 'google-plus-platform' );
						if ( 0 == $tag->childNodes->length ) {
							$img = $content_document->createElement( 'img' );
							$img->setAttribute( 'src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );
							$tag->setAttribute( 'data-lazy-widget', "google-plus-platform" );
							$tag->appendChild( $img );
						}
					}
				}
			}
			// Google Plus JS Version
			if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{.*\(\s*.*po\s*\.\s*src\s*=\s*["\']https://apis\s*.google\s*.com/js/platform.js["\'];.*}\s*\)\s*\(\s*\)\s*;~', $content, $matches ) ) {
				$tag_content = str_replace( "\n", '', $document->saveHTML( $tag ) );
				$tag_content = str_replace( "\r", '', $tag_content );
				$tag_content = str_replace( array( '<script>//', '//</script>' ), array(
					'<script>',
					'</script>',
				), $tag_content );
				rocket_footer_js_lazyload_script( $tag_content, 'google-plus-platform', $tag, $document, $content_document );
				/** @var DOMElement $tag */
				foreach (
					array(
						'//g:plusone',
						'//*[contains(concat(" ", normalize-space(@class), " "), " g-plusone ")]',
						'//*[contains(concat(" ", normalize-space(@class), " "), " g-plus ")]',
					) as $expression
				) {
					foreach ( $xpath->query( $expression ) as $tag ) {
						$tag->setAttribute( 'data-lazy-widget', 'google-plus-platform' );
						if ( 0 == $tag->childNodes->length ) {
							$img = $content_document->createElement( 'img' );
							$img->setAttribute( 'src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );
							$tag->setAttribute( 'data-lazy-widget', "google-plus-platform" );
							$tag->appendChild( $img );
						}
					}
				}

			}
			// Twitter
			if ( preg_match( '~(?:window\.twttr\s*=\s*\(|!|\()\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*\(\s*document\s*,\s*[\'"]script[\'"]\s*,\s*[\'"]twitter-wjs[\'"]\s*(?:\)\);|\);)~', $content, $matches ) ) {
				$tag_content = str_replace( "\n", '', $document->saveHTML( $tag ) );
				$tag_content = str_replace( "\r", '', $tag_content );
				$tag_content = str_replace( array( '<script>//', '//</script>' ), array(
					'<script>',
					'</script>',
				), $tag_content );
				rocket_footer_js_lazyload_script( $tag_content, 'twitter-sdk', $tag, $document, $content_document );
				/** @var DOMElement $tag */
				foreach (
					array(
						'twitter-share-button',
						'twitter-hashtag-button',
						'twitter-mention-button',
						'twitter-dm-button',
						'twitter-follow-button',
					) as $class
				) {
					foreach ( $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]' ) as $tag ) {
						$tag->setAttribute( 'data-lazy-widget', 'twitter-sdk' );
					}
				}
			}
			// Tumbler
			if ( preg_match( '~\(\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*\(\s*document\s*,\s*[\'"]script[\'"]\s*,\s*[\'"]tumblr-js[\'"]\s*(?:\)\);|\);)~', $content, $matches ) ) {
				$tag_content = str_replace( "\n", '', $document->saveHTML( $tag ) );
				$tag_content = str_replace( "\r", '', $tag_content );
				$tag_content = str_replace( array( '<script>//', '//</script>' ), array(
					'<script>',
					'</script>',
				), $tag_content );
				rocket_footer_js_lazyload_script( $tag_content, 'tumblr-share-button-widget', $tag, $document, $content_document );
				/** @var DOMElement $tag */
				foreach ( $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " tumblr-share-button ")]' ) as $tag ) {
					$tag->setAttribute( 'data-lazy-widget', 'twitter-sdk' );
				}
			}
			// Avada Google Maps Support
			if ( class_exists( 'Avada_GoogleMap' ) && Avada()->settings->get( 'status_gmap' ) ) {
				if ( preg_match( '~fusion_run_map_fusion_map_(\w+)~is', $content, $matches ) ) {
					if ( empty( $google_maps_script_id ) ) {
						$google_maps_script_id = 'avada_fusion_google_maps';
					}
					$sub_url = '';
					/** @var DOMElement $sub_tag */
					foreach ( $document->getElementsByTagName( 'script' ) as $sub_tag ) {
						$src = $sub_tag->getAttribute( 'src' );
						if ( false !== strpos( parse_url( $src, PHP_URL_PATH ), 'assets/js/infobox_packed.js' ) ) {
							$sub_url = $src;
							if ( $sub_tag->parentNode ) {
								$sub_tag->parentNode->removeChild( $sub_tag );
							}
						}
					}
					if ( ! empty( $sub_url ) ) {
						$new_script = $document->createElement( 'script', '(function(){(function check(){if(typeof google=="undefined")setTimeout(check,10);else{jQuery.getScript("' . $sub_url . '", function(){' . $content . '; if(document.readyState == "complete"){' . $matches[0] . '();}})}})()})();' );
					} else {
						$new_script = $document->createElement( 'script', '(function(){(function check(){if(typeof google=="undefined")setTimeout(check,10);else{' . $content . ';' . $matches[0] . '();}})()})();' );
					}
					$new_script->setAttribute( 'type', 'text/javascript' );
					$google_maps_script_content .= $document->saveHTML( $new_script );
					$tag->parentNode->removeChild( $tag );

					$element = $document->getElementById( "fusion_map_{$matches[1]}" );
					if ( ! empty( $element ) ) {
						$element->setAttribute( 'data-lazy-widget', $google_maps_script_id );
					}
				}
			}
			// Google Adsense
			if ( 'pagead2.googlesyndication.com' == parse_url( $src, PHP_URL_HOST ) ) {
				$sub_content = $document->saveHTML( $tag );
				$next_tag    = $tag;
				do {
					$next_tag = $next_tag->nextSibling;
				} while ( ! empty( $next_tag ) && ! ( XML_ELEMENT_NODE == $next_tag->nodeType && 'ins' == strtolower( $next_tag->tagName ) && false !== strpos( $next_tag->getAttribute( 'class' ), 'adsbygoogle' ) ) );
				$ad_node = $next_tag;
				if ( empty( $ad_node ) ) {
					$tag->setAttribute( 'data-no-minify', '1' );
					$next_tag = $tag;
				}
				do {
					$next_tag = $next_tag->nextSibling;
				} while ( ! ( XML_ELEMENT_NODE === $next_tag->nodeType && 'script' == strtolower( $next_tag->tagName ) && isset( $next_tag->textContent ) && false !== strpos( $next_tag->textContent, 'adsbygoogle' ) ) );
				$js_node = $next_tag;
				if ( ! empty( $ad_node ) ) {
					$sub_content .= $document->saveHTML( $js_node );
					$sub_content = str_replace( "\n", '', $sub_content );
					$sub_content = str_replace( "\r", '', $sub_content );
					$sub_content = str_replace( array( '<script>//', '//</script>' ), array(
						'<script>',
						'</script>',
					), $sub_content );
					rocket_footer_js_lazyload_script( $sub_content, "google-adsense-{$google_adsense_count}", $tag, $document, $content_document );
					$js_node->parentNode->removeChild( $js_node );
					$ad_node->setAttribute( 'data-lazy-widget', "google-adsense-{$google_adsense_count}" );
					$google_adsense_count ++;
				} else {
					$js_node->setAttribute( 'data-no-minify', '1' );
				}
			}
			// Amazon Ads
			if ( false !== strpos( $src, 'amazon-adsystem.com' ) ) {
				$prev_tag = $tag->previousSibling;
				while ( XML_ELEMENT_NODE !== $prev_tag->nodeType && 'script' != strtolower( $tag->tagName ) ) {
					$prev_tag = $prev_tag->previousSibling;
				}
				$sub_content = $document->saveHTML( $tag ) . $document->saveHTML( $prev_tag );
				$img         = $document->createElement( 'img' );
				$span        = $document->createElement( 'span' );
				$img->setAttribute( 'src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );
				$span->setAttribute( 'data-lazy-widget', "vk-share-{$vshare_counter}" );
				$span->appendChild( $img );
				$prev_tag->parentNode->removeChild( $prev_tag );
				$tag->parentNode->insertBefore( $span, $tag );
				rocket_footer_js_lazyload_script( $sub_content, "amazon-ads-{$amazon_ads_count}", $tag, $document, $content_document );
				$tag->setAttribute( 'data-lazy-widget', "amazon-ads-{$amazon_ads_count}" );
				$amazon_ads_count ++;
			}
			// Stumble Upon
			if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{.*[\'"]((?:https?:)?//platform\.stumbleupon\.com/1/widgets.js)[\'"].*\}\s*\)\s*\(\s*\)\s*;~', $content, $matches ) ) {
				$tag_content = str_replace( "\n", '', $document->saveHTML( $tag ) );
				$tag_content = str_replace( "\r", '', $tag_content );
				$tag_content = str_replace( array( '<script>//', '//</script>' ), array(
					'<script>',
					'</script>',
				), $tag_content );
				rocket_footer_js_lazyload_script( $tag_content, 'stumbleupon', $tag, $document );
				/** @var DOMElement $tag */
				foreach ( $document->getElementsByTagName( 'su:badge' ) as $tag ) {
					$tag->setAttribute( 'data-lazy-widget', 'stumbleupon' );
				}
				foreach ( $document->getElementsByTagName( 'su:follow' ) as $tag ) {
					$tag->setAttribute( 'data-lazy-widget', 'stumbleupon' );
				}
			}
			// VK.com
			if ( 'vk.com' == parse_url( $src, PHP_URL_HOST ) ) {
				$next_tag = $tag->nextSibling;
				while ( XML_ELEMENT_NODE !== $next_tag->nodeType && 'script' != strtolower( $tag->tagName ) ) {
					$next_tag = $next_tag->nextSibling;
				}
				if ( preg_match( '~document\s*.\s*write\s*\(\s*(VK\s*.\s*Share\s*.\s*button.*.\s*\))\s*\);~', $next_tag->textContent, $matches ) ) {
					$share_script = <<<JS
(function check () {
  if (typeof VK === 'undefined') {
    setTimeout(check, 10);
  }
  else {
    document.querySelector('[data-lazy-widget="vk-share-{$vshare_counter}"]').innerHTML = {$matches[1]};
  }
})();
JS;

					$share_script = $document->createElement( 'script', $share_script );
					$span         = $document->createElement( 'span' );
					$img          = $document->createElement( 'img' );
					$img->setAttribute( 'src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );
					$span->setAttribute( 'data-lazy-widget', "vk-share-{$vshare_counter}" );
					$span->appendChild( $img );
					if ( empty( $next_tag->nextSibling ) ) {
						$next_tag->parentNode->appendChild( $span );
					} else {
						$next_tag->parentNode->insertBefore( $next_tag->nextSibling, $span );
					}
					$tag_content = str_replace( "\n", '', $document->saveHTML( $tag ) );
					$tag_content = str_replace( "\r", '', $tag_content );
					$tag_content = str_replace( array( '<script>//', '//</script>' ), array(
						'<script>',
						'</script>',
					), $tag_content );

					rocket_footer_js_lazyload_script( $tag_content . $document->saveHTML( $share_script ), "vk-share-{$vshare_counter}", $tag, $document, $content_document );
					$next_tag->parentNode->removeChild( $next_tag );
					$vshare_counter ++;
				}
			}

		}
		if ( ! $lazy_load ) {
			// Google Adsense
			if ( 'pagead2.googlesyndication.com' == parse_url( $src, PHP_URL_HOST ) ) {
				$tag->setAttribute( 'data-no-minify', 1 );
				$next_tag = $tag->nextSibling;
				while ( XML_ELEMENT_NODE !== $next_tag->nodeType && 'script' != strtolower( $tag->tagName ) ) {
					$next_tag = $next_tag->nextSibling;
				}
				$next_tag->setAttribute( 'data-no-minify', 1 );
			}
			// Amazon Ads
			if ( false !== strpos( $src, 'amazon-adsystem.com' ) ) {
				$tag->setAttribute( 'data-no-minify', 1 );
				$prev_tag = $tag->previousSibling;
				while ( XML_ELEMENT_NODE !== $prev_tag->nodeType && 'script' != strtolower( $tag->tagName ) ) {
					$prev_tag = $prev_tag->previousSibling;
				}
				$prev_tag->setAttribute( 'data-no-minify', 1 );
			}
			// Google Plus
			if ( false !== strpos( $src, 'apis.google.com/js/platform.js' ) ) {
				$tag->setAttribute( 'data-no-minify', 1 );
			}
		}

		if ( $lazy_load ) {
			if ( ! empty( $google_maps_tag ) && ! empty( $google_maps_script_content ) ) {
				rocket_footer_js_lazyload_script( $document->saveHTML( $google_maps_tag ) . $google_maps_script_content, $google_maps_script_id, $google_maps_tag, $document, $content_document );
			}
			foreach ( $document->getElementsByTagName( 'iframe' ) as $tag ) {
				$data_src = $tag->getAttribute( 'data-src' );
				if ( empty( $data_src ) ) {
					$src = $tag->getAttribute( 'src' );
					if ( ! empty( $src ) ) {
						$tag->setAttribute( 'data-src', $src );
						$tag->removeAttribute( 'src' );
					}
				}
			}
		}

	}
	do_action_ref_array( 'rocket_footer_js_rewrite_js_loaders', $document );
}

/**
 * Rewrite script to be a comment in a div tag to be lazyloaded with a unique ID
 *
 * @param string      $html
 * @param string      $id
 * @param DOMElement  $tag
 * @param DOMDocument $document
 */
function rocket_footer_js_lazyload_script( $html, $id, $tag, $document, $content_document = null ) {
	if ( empty( $content_document ) ) {
		$content_document = $document;
	}
	if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
		$external_tag = $content_document->createElement( 'div' );
		$external_tag->appendChild( $content_document->createElement( 'WP_ROCKET_FOOTER_JS_LAZYLOAD_START' ) );
		$external_tag->appendChild( $content_document->createTextNode( $html ) );
		$external_tag->appendChild( $content_document->createElement( 'WP_ROCKET_FOOTER_JS_LAZYLOAD_END' ) );
	} else {
		$comment_tag  = $content_document->createComment( $html );
		$external_tag = $content_document->createElement( 'div' );
		$external_tag->appendChild( $comment_tag );
	}
	$external_tag->setAttribute( 'id', $id );
	if ( $content_document === $document ) {

		$tag->parentNode->insertBefore( $external_tag, $tag );
	} else {
		$content_document->getElementsByTagName( 'body' )->item( 0 )->appendChild( $external_tag );
	}
	$tag->parentNode->removeChild( $tag );
}

/**
 * Allow post-processing of individual remote scripts
 *
 * @since 1.2.4
 *
 * @param string      $url
 * @param string      $script
 * @param DOMDocument $document
 * @param array       $tags
 *
 * @return mixed
 */
function rocket_footer_js_process_remote_script( $url, $script, $document, $tags ) {
	global $rocket_async_css_file;
	if ( 'embed.tawk.to' == parse_url( $url, PHP_URL_HOST ) ) {
		if ( preg_match( '~\w\.src\s*=\s*"(https://cdn\.jsdelivr\.net/emojione/[\d\.]+/lib/js/emojione\.min\.js)"\s*;~', $script, $matches ) ) {
			$script          = str_replace( $matches[0], '', $script );
			$emojione_script = $document->createElement( 'script' );
			$emojione_script->setAttribute( 'type', 'text/javascript' );
			$emojione_script->setAttribute( 'src', $matches[1] );
			$tags[] = $emojione_script;
		}
		if ( ! empty( $rocket_async_css_file ) ) {
			if ( class_exists( 'Rocket_Async_Css' ) && method_exists( 'Rocket_Async_Css', 'minify_remote_file' ) && preg_match( '~\w\.href\s*=\s*"(https://cdn\.jsdelivr\.net/emojione/[\d\.]+/assets/css/emojione\.min\.css)"\s*;~', $script, $matches ) ) {
				$script        = str_replace( $matches[0], '', $script );
				$style         = rocket_footer_js_get_content( $rocket_async_css_file );
				$item_cache_id = md5( $matches[1] );
				$item_cache_id = 'wp_rocket_footer_js_script_' . $item_cache_id;
				$file          = get_transient( $item_cache_id );
				if ( empty( $file ) ) {
					$file = rocket_footer_js_remote_fetch( $matches[1] );
					// Do nothing on error
					if ( ! empty( $file ) ) {
						$css_part = Rocket_Async_Css::get_instance()->minify_remote_file( $url, $file );
						$style    .= $css_part;
						set_transient( $item_cache_id, $css_part, get_rocket_purge_cron_interval() );
					}
				}
				if ( ! empty( $file ) ) {
					rocket_put_content( $rocket_async_css_file, $style );
				}
			}
		}
	}

	return apply_filters_ref_array( 'rocket_footer_js_process_remote_script', array( $script, $url, $tags ) );
}

function rocket_footer_js_remote_fetch( $url ) {
	$debug = rocket_footer_js_debug_enabled();
	$file  = wp_remote_get( $url, array(
		'user-agent' => 'WP-Rocket',
		'sslverify'  => false,
	) );
	if ( ! ( $file instanceof \WP_Error || ( is_array( $file ) && ( empty( $file['response']['code'] ) || ! in_array( $file['response']['code'], array(
					200,
					304,
				) ) ) )
	)
	) {
		return $file['body'];
	} else {
		if ( $debug ) {
			error_log( 'URL: ' . $url . ' Status:' . ( $file instanceof \WP_Error ? 'N/A' : $file['code'] ) . ' Error:' . ( $file instanceof \WP_Error ? $file->get_error_message() : 'N/A' ) );
		}

		return false;
	}
}

/**
 * @param $url      string
 * @param $script   string
 * @param $document \DOMDocument
 * @param $tags     array
 *
 * @return mixed
 */
function rocket_footer_js_process_local_script( $url, $script, $document, &$tags ) {
	// Extract Facebook Pixel from "Pixel Your Site" plugin
	if ( function_exists( 'pys_free_init' ) && set_url_scheme( WP_PLUGIN_URL . '/pixelyoursite/js/public.js' ) == $url ) {
		if ( preg_match( '~!?function\s*\(\s*f\s*,\s*b\s*,\s*e\s*,\s*v\s*,\s*n\s*,\s*t\s*,\s*s\s*\)\s*{\s*if\s*\(\s*f\s*\.\s*fbq\s*\)\s*return\s*;\s*n\s*=\s*f\s*.\s*fbq\s*=\s*function.*\s*\(\s*window\s*,\s*document\s*,\s*\'script\'\s*,\s*\'((?:https?:)?//connect.facebook.net/[\w_]+/fbevents.js)\'\s*\)\s*;~s', $script, $matches ) ) {
			$external_tag = $document->createElement( 'script', '(function(a){a.fbq||(n=a.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)},a._fbq||(a._fbq=n));n.push=n;n.disableConfigLoading=!0;n.loaded=!0;n.version="2.0";n.queue=[]})(window);' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$tags[]       = $external_tag;
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', $matches[1] );
			$external_tag->setAttribute( 'async', false );
			$tags[]       = $external_tag;
			$external_tag = $document->createElement( 'script', 'fbq.registerPlugin("config" + pys_events.name, {__fbEventsPlugin: 1,plugin: function(f, i){i.configLoaded(pys_events.name);}});' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$tags[]       = $external_tag;
			$external_tag = $document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', str_replace( 'fbevents.js', 'fbevents.plugins.identity.js', $matches[1] ) );
			$external_tag->setAttribute( 'async', false );
			$tags[] = $external_tag;
			$script = str_replace( $matches[0], '', $script );
		}
	}
	// Extract scripts from WooCommerce Social Media Share Buttons plugin
	if ( function_exists( 'toastie_wc_smsb_social_init' ) && set_url_scheme( WP_PLUGIN_URL . '/woocommerce-social-media-share-buttons/smsb_script.js' ) == $url ) {
		$script = str_replace( "\n", '', $script );
		if ( preg_match_all( '~\\(function.*(?:\)\)|}\)\(\));~U', $script, $matches ) ) {
			$doc = new DOMDocument();
			foreach ( $matches[0] as $match ) {
				$tag = $doc->createElement( 'script' );
				$cm  = $doc->createTextNode( "\n//" );
				$ct  = $doc->createCDATASection( "\n" . $match . "\n//" );
				$tag->appendChild( $cm );
				$tag->appendChild( $ct );
				$doc->appendChild( $tag );
				$script = str_replace( $match, '', $script );
			}
			rocket_footer_js_rewrite_js_loaders( $doc, $document );
		}

	}

	return apply_filters_ref_array( 'rocket_footer_js_process_local_script', array( $script, $url, $document, $tags ) );
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
/**
 *
 */
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
 *
 * @since 1.0.0
 *
 */
function rocket_footer_js_activate() {
	if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
		activate_plugins( 'wp-rocket/wp-rocket.php' );
	}
	rocket_footer_js_delete_cache_branch();
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
	if ( class_exists( 'CWS_PageLinksTo' ) ) {
		add_action( 'init', 'rocket_footer_js_disable_page_links_to_buffer', 12 );
	}
	if ( class_exists( 'N2Pluggable' ) ) {
		N2Pluggable::addAction( 'systemglobal', 'rocket_footer_js_n2pluggable_disable' );
		N2Settings::init();
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
	if ( rocket_footer_js_lazy_load_enabled() ) {
		add_action( 'wp_enqueue_scripts', 'rocket_footer_js_scripts' );
	}
	if ( class_exists( 'Ga_Helper' ) ) {
		if ( ! is_admin() ) {
			remove_action( 'wp_footer', 'Ga_Frontend::insert_ga_script' );
			if ( Ga_Helper::can_add_ga_code() || Ga_Helper::is_all_feature_disabled() ) {
				add_action( 'wp_enqueue_scripts', 'rocket_footer_js_googleanalytics_enqueue' );
			}
		}
	}
}

/**
 * Conditionally enqueue lazyload widget script
 */
function rocket_footer_js_scripts() {
	global $a3_lazy_load_global_settings;
	$lazy_load = false;
	if ( ! empty( $a3_lazy_load_global_settings ) ) {
		wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', __FILE__ ), array( 'jquery-lazyloadxt' ) );
		$lazy_load = true;
	}
	if ( ! $lazy_load ) {
		wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', __FILE__ ), array( 'lazy-load-xt-script' ) );
	}
}

/**
 * Disable minify on AMP pages
 *
 * @since 1.2.2
 *
 */
function rocket_footer_js_wp() {
	if ( defined( 'AMP_QUERY_VAR' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		remove_filter( 'rocket_buffer', 'rocket_footer_js_inline', PHP_INT_MAX );
	}
}

/**
 * Check if lazy load is enabled
 *
 * @since 1.2.4
 *
 * @return bool
 */
function rocket_footer_js_lazy_load_enabled() {
	global $a3_lazy_load_global_settings;
	$lazy_load = false;
	if ( class_exists( 'A3_Lazy_Load' ) ) {
		$lazy_load = (bool) $a3_lazy_load_global_settings['a3l_apply_lazyloadxt'];
	}
	if ( class_exists( 'LazyLoadXT' ) ) {
		$lazy_load = true;
	}

	return $lazy_load;
}

/**
 * @param $file
 *
 * @since 1.0.0
 *
 * @return bool|string
 */
function rocket_footer_js_get_content( $file ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
	$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

	return $direct_filesystem->get_contents( $file );
}

function rocket_footer_js_minify( $script ) {
	$script = rocket_minify_inline_js( $script );
	$script = preg_replace( '~/\*!?\s+.*\*/~sU', '', $script );

	return $script;
}


/**
 * @param $scripts
 *
 * @since 1.1.13
 */
function rocket_footer_deasync_zxcvbn( $scripts ) {

	/** @var WP_Scripts $scripts */
	if ( ! empty( $scripts->registered['zxcvbn-async'] ) ) {
		$scripts->registered['zxcvbn-async']->src   = includes_url( '/js/zxcvbn.min.js' );
		$scripts->registered['zxcvbn-async']->extra = array();
	}
}

/**
 *
 */
function rocket_footer_js_disable_page_links_to_buffer() {
	remove_action( 'wp_enqueue_scripts', array( CWS_PageLinksTo::$instance, 'start_buffer' ), - 9999 );
	remove_action( 'wp_head', array( CWS_PageLinksTo::$instance, 'end_buffer' ), 9999 );
}

function rocket_footer_js__get_cache_fragment( $path ) {
	if ( ! in_array( 'cache', $path ) ) {
		array_unshift( $path, 'cache' );
	}

	return get_transient( ROCKET_FOOTER_JS_TRANSIENT_PREFIX . implode( '_', $path ) );
}

function rocket_footer_js_delete_cache_branch( $path = array() ) {
	if ( is_array( $path ) ) {
		if ( ! empty( $path ) ) {
			$path = ROCKET_FOOTER_JS_TRANSIENT_PREFIX . implode( '_', $path ) . '_';
		} else {
			$path = ROCKET_FOOTER_JS_TRANSIENT_PREFIX;
		}
	}
	$counter_transient = "{$path}cache_count";
	$counter           = get_transient( $counter_transient );

	if ( is_null( $counter ) || false === $counter ) {
		delete_transient( rtrim( $path, '_' ) );

		return;
	}
	for ( $i = 1; $i <= $counter; $i ++ ) {
		$transient_name = "{$path}cache_{$i}";
		$cache          = get_transient( "{$path}cache_{$i}" );
		if ( ! empty( $cache ) ) {
			foreach ( $cache as $sub_branch ) {
				rocket_footer_js_delete_cache_branch( "{$sub_branch}_" );
			}
			delete_transient( $transient_name );
		}
	}
	delete_transient( $counter_transient );
}

function rocket_footer_js_update_cache_fragment( $path, $value ) {
	if ( ! in_array( 'cache', $path ) ) {
		array_unshift( $path, 'cache' );
	}
	rocket_footer_js_build_cache_tree( array_slice( $path, 0, count( $path ) - 1 ) );
	rocket_footer_js_update_tree_branch( $path, $value );
}

function rocket_footer_js_build_cache_tree( $path ) {
	$levels = count( $path );
	$expire = get_rocket_purge_cron_interval();
	for ( $i = 0; $i < $levels; $i ++ ) {
		$transient_id       = ROCKET_FOOTER_JS_TRANSIENT_PREFIX . implode( '_', array_slice( $path, 0, $i + 1 ) );
		$transient_cache_id = $transient_id;
		if ( 'cache' != $path[ $i ] ) {
			$transient_cache_id .= '_cache';
		}
		$transient_cache_id .= '_1';
		$cache              = get_transient( $transient_cache_id );
		$transient_value    = array();
		if ( $i + 1 < $levels ) {
			$transient_value[] = ROCKET_FOOTER_JS_TRANSIENT_PREFIX . implode( '_', array_slice( $path, 0, $i + 2 ) );
		}
		if ( ! is_null( $cache ) && false !== $cache ) {
			$transient_value = array_unique( array_merge( $cache, $transient_value ) );
		}
		set_transient( $transient_cache_id, $transient_value, $expire );
		$transient_counter_id = $transient_id;
		if ( 'cache' != $path[ $i ] ) {
			$transient_counter_id .= '_cache';
		}
		$transient_counter_id .= '_count';
		$transient_counter    = get_transient( $transient_counter_id );
		if ( is_null( $transient_counter ) || false === $transient_counter ) {
			set_transient( $transient_counter_id, 1, $expire );
		}
	}
}

function rocket_footer_js_update_tree_branch( $path, $value ) {
	$branch            = ROCKET_FOOTER_JS_TRANSIENT_PREFIX . implode( '_', $path );
	$parent_path       = array_slice( $path, 0, count( $path ) - 1 );
	$parent            = ROCKET_FOOTER_JS_TRANSIENT_PREFIX . implode( '_', $parent_path );
	$counter_transient = $parent;
	$cache_transient   = $parent;
	if ( 'cache' != end( $parent_path ) ) {
		$counter_transient .= '_cache';
		$cache_transient   .= '_cache';
	}
	$counter_transient .= '_count';
	$counter           = (int) get_transient( $counter_transient );
	$cache_transient   .= "_{$counter}";
	$cache             = get_transient( $cache_transient );
	$count             = count( $cache );
	$cache_keys        = array_flip( $cache );
	$expire            = get_rocket_purge_cron_interval();
	if ( ! isset( $cache_keys[ $branch ] ) ) {
		if ( $count >= apply_filters( 'rocket_footer_js_max_branch_length', 50 ) ) {
			$counter ++;
			set_transient( $counter_transient, $counter, $expire );
			$cache_transient = $parent;
			if ( 'cache' != end( $parent_path ) ) {
				$cache_transient .= '_cache';
			}
			$cache_transient .= "_{$counter}";
			$cache           = array();
		}
		$cache[] = $branch;
		set_transient( $cache_transient, $cache, $expire );
	}
	set_transient( $branch, $value, $expire );
}

/**
 * @param $post
 */
function rocket_footer_js_prune_post_transients( $post ) {
	rocket_footer_js_delete_cache_branch( array( 'cache', "post_{$post->ID}" ) );
}

function rocket_footer_js_prune_term_transients( $term ) {
	rocket_footer_js_delete_cache_branch( array( 'cache', "term_{$term->term_id}" ) );
}

function rocket_footer_js_prune_url_transients( $url ) {
	$url = md5( $url );
	rocket_footer_js_delete_cache_branch( array( 'cache', "url_{$url}" ) );
}

function rocket_footer_js_n2pluggable_disable( $referenceKey, &$rows ) {
	foreach ( array_keys( $rows ) as $key ) {
		if ( in_array( $rows[ $key ]['referencekey'], array(
			'async',
			'combine-js',
			'minify-js',
			'protocol-relative',
			'curl',
		) ) ) {
			$rows[ $key ]['value'] = 0;
		}
	}
}

function rocket_footer_js_googleanalytics_enqueue() {
	$web_property_id = Ga_Frontend::get_web_property_id();
	if ( Ga_Helper::should_load_ga_javascript( $web_property_id ) ) {
		$javascript = Ga_View_Core::load( 'ga_code', array(
			'data' => array(
				Ga_Admin::GA_WEB_PROPERTY_ID_OPTION_NAME => $web_property_id,
			),
		), true );
		$javascript = strip_tags( $javascript );
		wp_add_inline_script( 'jquery-core', $javascript );
	}
}

/*
 * wp_print_scripts and wp_footer hooks can be used to force enqueue JS in the footer, but may not be compatible with bad plugins that don't register their JS properly. Will remain here for the time that this may improve. DOMDocument parsing will be used until then.
 */
//add_action( 'wp_print_scripts', 'rocket_force_js_footer' );
//add_action( 'wp_footer', 'rocket_remove_empty_footer_js', 21 );
add_action( 'plugins_loaded', 'rocket_footer_js_plugins_loaded' );

add_action( 'init', 'rocket_footer_js_init', 11 );
add_action( 'wp', 'rocket_footer_js_wp' );
if ( ! is_admin() && ! in_array( $pagenow, array( 'wp-login.php', 'wp-signup.php' ) ) ) {
	// Ensure zxcvbn is loaded normally, not async so it gets minified
	add_action( 'wp_default_scripts', 'rocket_footer_deasync_zxcvbn' );
}
add_filter( 'rocket_buffer', 'rocket_footer_js_inline', PHP_INT_MAX );
add_filter( 'pre_get_rocket_option_minify_js_combine_all', '__return_zero' );
//Only override JS Minify option of front end
if ( ! is_admin() ) {
	add_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
	add_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
}


add_action( 'after_rocket_clean_domain', 'rocket_footer_js_delete_cache_branch', 10, 0 );
add_action( 'after_rocket_clean_post', 'rocket_footer_js_prune_post_transients' );
add_action( 'after_rocket_clean_term', 'rocket_footer_js_prune_term_transients' );
add_action( 'after_rocket_clean_files', 'rocket_footer_js_prune_url_transients' );
register_activation_hook( __FILE__, 'rocket_footer_js_activate' );
register_deactivation_hook( __FILE__, 'rocket_footer_js_delete_cache_branch', 10, 0 );

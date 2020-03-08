<?php


namespace Rocket\Footer;


use ComposePress\Core\Abstracts\Plugin;
use Rocket\Footer\JS\Cache\Manager;
use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\DOMElement;
use Rocket\Footer\JS\Integration\Manager as IntegrationManager;
use Rocket\Footer\JS\Lazyload\Manager as LazyloadManager;
use Rocket\Footer\JS\Request;
use Rocket\Footer\JS\Rewrite\Manager as RewriteManager;
use Rocket\Footer\JS\Util;

/**
 * Class JS
 *
 * @package Rocket\Footer
 */
class JS extends Plugin {
	/**
	 * Plugin version
	 */
	const VERSION = '3.2.3';
	/**
	 *
	 */
	const TRANSIENT_PREFIX = 'rocket_footer_js_';

	const PLUGIN_SLUG = 'rocket-footer-js';


	/**
	 * @var \Rocket\Footer\JS\Rewrite\Manager
	 */
	private $rewrite_manager;
	/**
	 * @var \Rocket\Footer\JS\Integration\Manager
	 */
	private $integration_manager;
	/**
	 * @var \Rocket\Footer\JS\Lazyload\Manager
	 */
	private $lazyload_manager;
	/**
	 * @var \Rocket\Footer\JS\Request
	 */
	private $request;
	/**
	 * @var \Rocket\Footer\JS\Cache\Manager
	 */
	private $cache_manager;

	/**
	 * @var array
	 */
	private $enqueued_variable_tags = [];
	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	private $document;

	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	private $script_document;

	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	private $variable_document;

	/**
	 * @var \SplObjectStorage
	 */
	private $node_map;
	/**
	 * @var array
	 */
	private $cache_list = [];
	/**
	 * @var string
	 */
	private $js = '';
	/**
	 * @var string
	 */
	private $cache = '';
	/**
	 * @var array
	 */
	private $urls = [];
	/**
	 * @var string
	 */
	private $home = '';
	/**
	 * @var string
	 */
	private $domain = '';
	/**
	 * @var array
	 */
	private $cdn_domains = [];
	/**
	 * @var DOMElement
	 */
	private $body;

	/**
	 * @var DOMCollection
	 */
	private $dom_collection;
	/**
	 * @var \Rocket\Footer\JS\Util
	 */
	private $util;

	private $cache_hash;

	private $excluded_file_regex;

	/**
	 * JS constructor.
	 *
	 * @param \Rocket\Footer\JS\Rewrite\Manager     $rewrite_manager
	 * @param \Rocket\Footer\JS\Integration\Manager $integration_manager
	 * @param \Rocket\Footer\JS\Lazyload\Manager    $lazyload_manager
	 * @param \Rocket\Footer\JS\Request             $request
	 * @param \Rocket\Footer\JS\Cache\Manager       $cache_manager
	 * @param \Rocket\Footer\JS\DOMDocument         $document
	 * @param \Rocket\Footer\JS\DOMDocument         $variable_document
	 * @param \Rocket\Footer\JS\DOMDocument         $script_document
	 * @param \Rocket\Footer\JS\Util                $util
	 */
	public function __construct(
		RewriteManager $rewrite_manager,
		IntegrationManager $integration_manager,
		LazyloadManager $lazyload_manager,
		Request $request, Manager $cache_manager,
		DOMDocument $document,
		DOMDocument $variable_document,
		DOMDocument $script_document,
		Util $util
	) {
		$this->rewrite_manager     = $rewrite_manager;
		$this->integration_manager = $integration_manager;
		$this->lazyload_manager    = $lazyload_manager;
		$this->request             = $request;
		$this->cache_manager       = $cache_manager;
		$this->variable_document   = $variable_document;
		$this->script_document     = $script_document;
		$this->node_map            = new \SplObjectStorage();
		$this->document            = $document;
		$this->util                = $util;
		parent::__construct();
	}

	/**
	 * @return \Rocket\Footer\JS\Rewrite\Manager
	 */
	public function get_rewrite_manager() {
		return $this->rewrite_manager;
	}

	/**
	 * @return \Rocket\Footer\JS\Integration\Manager
	 */
	public function get_integration_manager() {
		return $this->integration_manager;
	}

	/**
	 * @return \Rocket\Footer\JS\Lazyload\Manager
	 */
	public function get_lazyload_manager() {
		return $this->lazyload_manager;
	}

	/**
	 * @return \Rocket\Footer\JS\Request
	 */
	public function get_request() {
		return $this->request;
	}

	/**
	 *
	 */
	public function init() {
		if ( ! $this->get_dependancies_exist() ) {
			return;
		}
		//Get home URL
		$this->home = set_url_scheme( home_url() );
		// Get our domain
		$this->domain = parse_url( $this->home, PHP_URL_HOST );

		parent::init();
	}

	/**
	 * @return bool
	 */
	protected function get_dependancies_exist() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$error = false;
		if ( validate_plugin( 'wp-rocket/wp-rocket.php' ) ) {
			$error = true;
			add_action( 'admin_notices', [ $this, 'activate_error_no_wprocket' ] );
		} else if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			$error = true;
			add_action( 'admin_notices', [ $this, 'activate_error_wprocket_inactive' ] );
		}
		if ( ! class_exists( 'DOMDocument' ) ) {
			$error = true;
			add_action( 'admin_notices', [ $this, 'activate_error_no_domdocument' ] );
		}
		if ( ! did_action( 'wp_rocket_loaded' ) ) {
			$error = true;
		}

		return ! $error;
	}

	/**
	 * @return \Rocket\Footer\JS\Cache\Manager
	 */
	public function get_cache_manager() {
		return $this->cache_manager;
	}

	/**
	 * @param $buffer
	 *
	 * @return mixed
	 */
	public function process_buffer( $buffer ) {
		$this->disable_option_overrides();
		/** @noinspection NotOptimalIfConditionsInspection */
		if ( get_rocket_option( 'minify_js' ) && ! ( defined( 'DONOTMINIFYJS' ) && DONOTMINIFYJS ) && ! is_rocket_post_excluded_option( 'minify_js' ) ) {
			/** @noinspection UsageOfSilenceOperatorInspection */
			if ( ! @$this->document->loadHTML( $buffer ) ) {
				return $buffer;
			}

			$this->body = $this->document->getElementsByTagName( 'body' )->item( 0 );
			$this->normalize_cdn_domains();

			do_action( 'rocket_footer_js_do_rewrites' );
			do_action( 'rocket_footer_js_do_lazyload' );

			$this->find_localized_scripts();
			$this->build_script_list();
			$this->fetch_cache();
			$filename = '';
			if ( empty( $this->cache ) ) {
				$filename = $this->get_cache_filename();
			}

			$this->cleanup_nodes();

			$this->process_scripts();

			$this->build_inline_scripts();

			extract( $this->write_cache( $filename ), EXTR_OVERWRITE );

			/** @var string $src */
			$this->add_main_script( $src );

			$this->fix_old_libxml();

			//Get HTML
			$buffer = $this->document->saveHTML();

			$buffer = $this->do_minify_html( $buffer );
		}

		return $buffer;
	}

	/**
	 *
	 */
	protected function disable_option_overrides() {
		remove_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
		remove_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
		remove_filter( 'pre_get_rocket_option_lazyload_iframes', '__return_zero' );
		remove_filter( 'pre_get_rocket_option_lazyload', '__return_zero' );
	}

	/**
	 *
	 */
	protected function normalize_cdn_domains() {
		// Remote fetch external scripts
		$this->cdn_domains = get_rocket_cdn_cnames( [
			'all',
			'css',
			'js',
			'css_and_js',
			'images',
		] );
		// Get the hostname for each CDN CNAME
		foreach ( array_keys( (array) $this->cdn_domains ) as $index ) {
			$cdn_domain       = &$this->cdn_domains[ $index ];
			$cdn_domain_parts = parse_url( $cdn_domain );
			if ( empty( $cdn_domain_parts['host'] ) ) {
				$cdn_domain       = "//{$cdn_domain}";
				$cdn_domain       = set_url_scheme( $cdn_domain );
				$cdn_domain_parts = parse_url( $cdn_domain );
			}
			$cdn_domain = $cdn_domain_parts['host'];
		}
		// Cleanup
		unset( $cdn_domain_parts, $cdn_domain );
	}

	/**
	 * Get all localized scripts
	 *
	 * @return void
	 */
	protected function find_localized_scripts() {
		foreach ( array_unique( wp_scripts()->queue ) as $item ) {
			$data = wp_scripts()->print_extra_script( $item, false );
			if ( ! empty( $data ) ) {
				$this->enqueued_variable_tags[] = '/* <![CDATA[ */' . $data . '/* ]]> */';
			}
		}
	}

	/**
	 *
	 */
	protected function build_script_list() {
		foreach ( $this->document->getElementsByTagName( 'script' ) as $tag ) {
			/** @var DOMElement $tag */
			if ( '1' == $tag->getAttribute( 'data-no-minify' ) || in_array( $tag->getAttribute( 'type' ), apply_filters( 'rocket_footer_js_exclude_tag_types', [
					'x-tmpl-mustache',
					'text/x-handlebars-template',
					'text/template',
					'text/html',
					'text/css',
				] ), true )
			) {
				continue;
			}
			if ( in_array( str_replace( "\n", '', $tag->textContent ), $this->enqueued_variable_tags ) ) {
				$this->variable_document->appendChild( $tag );
			} else {
				// Skip ld+json and leave it in the header
				if ( 'application/ld+json' !== $tag->getAttribute( 'type' ) ) {
					$this->script_document->appendChild( $tag );
					$src = $tag->getAttribute( 'src' );
					if ( ! empty( $src ) ) {
						if ( $this->is_file_excluded( $src ) ) {
							continue;
						}
						$this->cache_list['external'][] = $src;
					} else if ( ! empty( $tag->textContent ) ) {
						$this->cache_list['inline'][] = $tag->textContent;
					}
				}
			}
		}
	}

	private function is_file_excluded( $file ) {
		$found = preg_match( '#^(' . $this->get_excluded_files() . ')$#', $file );

		return ! empty( $found );
	}

	private function get_excluded_files() {

		if ( ! $this->excluded_file_regex ) {
			$excluded_files = get_rocket_option( 'exclude_js' );

			/**
			 * Filter JS files to exclude from minification/concatenation.
			 *
			 * @param array $js_files List of excluded JS files.
			 *
			 * @since 2.6
			 *
			 */
			$excluded_files = apply_filters( 'rocket_exclude_js', $excluded_files );

			if ( empty( $excluded_files ) ) {
				return '';
			}

			foreach ( $excluded_files as $i => $excluded_file ) {
				// Escape characters for future use in regex pattern.
				$excluded_files[ $i ] = str_replace( '#', '\#', $excluded_file );
			}

			$this->excluded_file_regex = implode( '|', $excluded_files );
		}

		return $this->excluded_file_regex;
	}


	/**
	 *
	 */
	protected function fetch_cache() {
		if ( ! apply_filters( 'rocket_footer_js_save_cache', true ) ) {
			$this->cache = false;

			return;
		}
		$this->cache = $this->get_cache_fragment( $this->get_cache_id() );

		// Cached file is gone, we dont have cache
		if ( ! empty( $this->cache ) && ! file_exists( $this->cache  ['filename'] ) ) {
			$this->cache = false;
		}
	}

	private function get_cache_fragment( $cache_id ) {
		if ( apply_filters( 'rocket_footer_js_save_cache', true ) ) {
			return $this->cache_manager->get_store()->get_cache_fragment( $cache_id );
		}

		return false;
	}

	/**
	 * @return array
	 */
	protected function get_cache_id() {
		$post_cache_id_hash = $this->get_cache_hash();
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

		return apply_filters( 'rocket_footer_js_get_cache_id', $post_cache_id );
	}

	/**
	 * @return string
	 */
	protected function get_cache_hash() {
		if ( null === $this->cache_hash ) {
			$this->cache_hash = md5( serialize( $this->cache_list ) );
		}

		return $this->cache_hash;
	}

	/**
	 * @return string
	 */
	protected function get_cache_filename() {
		$js_key     = get_rocket_option( 'minify_js_key' );
		$cache_path = $this->get_cache_path();
		// If we have a user logged in, include user role in filename to be unique as we may have user only JS content. Otherwise file will be a hash of (minify-global-[js_key]-[content_hash]).js
		if ( is_user_logged_in() ) {
			$filename = $cache_path . md5( 'minify-' . wp_get_current_user()->roles[0] . '-' . $js_key . '-' . $this->get_cache_hash() ) . '.js';
		} else {
			$filename = $cache_path . md5( 'minify-global' . '-' . $js_key . '-' . $this->get_cache_hash() ) . '.js';
		}
		// Create post_cache dir if needed
		if ( ! is_dir( $cache_path ) ) {
			rocket_mkdir_p( $cache_path );
		}

		return $filename;
	}

	/**
	 * @return string
	 */
	public function get_cache_path() {
		return WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/';
	}

	/**
	 *
	 */
	protected function cleanup_nodes() {
		/** @var DOMElement $tag */
		// Remove all elements from DOM
		foreach (
			array_merge(
				iterator_to_array( $this->script_document->get_script_tags() ),
				iterator_to_array( $this->variable_document->get_script_tags() )
			) as $tag
		) {
			if ( ! empty( $this->node_map[ $tag ] ) ) {
				$this->node_map[ $tag ]->remove();
			}
		}
	}

	/**
	 *
	 */
	protected function process_scripts() {
		$this->dom_collection = new DOMCollection( $this->script_document, 'script' );
		while ( $this->dom_collection->valid() ) {
			$this->process_script();
			$this->dom_collection->next();
		}
	}

	/**
	 *
	 */
	protected function process_script() {
		// If the last character is not a semicolon, and we have content,add one to prevent syntax errors
		if ( 0 < strlen( $this->js ) && ! in_array( $this->js[ strlen( $this->js ) - 1 ], [ ';', "\n" ] ) ) {
			$this->js .= ";\n";
		}
		//Decode html entities
		$src = $this->dom_collection->current()->getAttribute( 'src' );
		if ( ! empty( $src ) ) {
			$this->process_external_script();

			return;
		}
		$this->process_inline_script();
	}

	/**
	 *
	 */
	protected function process_external_script() {
		$src = $this->dom_collection->current()->getAttribute( 'src' );
		$src = html_entity_decode( preg_replace( '/((?<!&)#.*;)/', '&$1', $src ) );
		if ( empty( $this->cache ) ) {
			if ( 0 === strpos( $src, '//' ) ) {
				//Handle no protocol urls
				$src = rocket_add_url_protocol( $src );
			}
			//Has it been processed before?
			if ( ! in_array( $src, $this->urls ) ) {
				// Get host of tag source
				$src_host = parse_url( $src, PHP_URL_HOST );
				// Being remote is defined as not having our home url and not being in the CDN list. However if the file does not have a JS extension, assume its a dynamic script generating JS, so we need to web fetch it.
				if ( 0 != strpos( $src, '/' ) && ( ( $src_host != $this->domain && ! in_array( $src_host, $this->cdn_domains ) ) || 'js' !== pathinfo( parse_url( $src, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ) {
					$this->process_remote_script( $src );
					$this->urls[] = $src;

					return;
				}

				$this->process_local_script( $src );
				$this->urls[] = $src;
			}
		}
	}

	/**
	 * @param string $src
	 *
	 * @internal param DOMElement $tag
	 */
	protected function process_remote_script( $src ) {
		// Check item cache
		$item_cache_id = [ md5( $src ) ];
		$item_cache    = $this->get_cache_fragment( $item_cache_id );
		// Only run if there is no item cache
		if ( empty( $item_cache ) ) {
			$file = $this->remote_fetch( $src );
			// Catch Error
			if ( ! empty( $file ) ) {
				$js_part_cache = apply_filters( 'rocket_footer_js_process_remote_script', $file, $src );
				$js_part       = $this->minify( $js_part_cache );
				if ( $js_part_cache != $file && apply_filters( 'rocket_footer_js_reprocess_remote_script', true, $file, $src ) ) {
					$js_part_cache = $file;
					$js_part_cache = $this->minify( $js_part_cache );
				} else {
					$js_part_cache = $js_part;
				}
				$this->update_cache_fragment( $item_cache_id, $js_part_cache );
				$this->js .= $js_part;
			}
		} else {
			$js_part = $item_cache;
			if ( apply_filters( 'rocket_footer_js_reprocess_remote_script', true, $js_part, $src ) ) {
				$js_part = apply_filters( 'rocket_footer_js_process_remote_script', $js_part, $src );
			}
			$this->js .= $js_part;
		}
	}

	/**
	 * @param $url
	 *
	 * @return bool|string
	 */
	public function remote_fetch( $url, $method = 'get', $args = [] ) {
		$func = "wp_remote_{$method}";
		if ( ! function_exists( $func ) ) {
			return false;
		}
		$file = $func( $url, array_merge_recursive( [
			'user-agent' => 'WP-Rocket',
			'sslverify'  => false,
		], $args ) );
		if ( ! ( $file instanceof \WP_Error || ( is_array( $file ) && ( empty( $file['response']['code'] ) || ! in_array( $file['response']['code'], array(
						200,
						304,
					) ) ) )
		)
		) {
			return $file['body'];
		}

		return false;
	}

	/**
	 * @param string $script
	 *
	 * @return string
	 */
	protected function minify( $script ) {
		$closure_url   = 'https://closure-compiler.appspot.com/compile';
		$run_closure   = ( defined( "ROCKET_FOOTER_JS_ENABLE_CLOSURE_COMPILER" ) && ROCKET_FOOTER_JS_ENABLE_CLOSURE_COMPILER );
		$closure_error = ! $run_closure;
		$result        = null;
		if ( $run_closure ) {
			$args   = [
				'body' => [
					'js_code'           => $script,
					'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
					'output_info'       => 'errors',
					'output_format'     => 'json',
				],
			];
			$result = $this->remote_fetch( $closure_url, 'post', $args );

			if ( empty( $result ) ) {
				$closure_error = true;
			}
			if ( ! empty( $result ) ) {
				$json = json_decode( $result );
				if ( ! empty( $json->errors ) ) {
					$closure_error = true;
				}
				if ( empty( $json->errors ) ) {
					$script = $json->compiledCode;
				}
			}
		}
		if ( ! $closure_error ) {
			return $script;
		}
		if ( $run_closure ) {
			$closure_error                     = false;
			$args['body']['compilation_level'] = 'WHITESPACE_ONLY';

			$result = $this->remote_fetch( $closure_url, 'post', $args );

			if ( empty( $result ) ) {
				$closure_error = true;
			}
			if ( ! empty( $result ) ) {
				$json = json_decode( $result );
				if ( ! empty( $json->errors ) ) {
					$closure_error = true;
				}
				if ( empty( $json->errors ) ) {
					$script = $json->compiledCode;
				}
			}
			if ( ! $closure_error ) {
				return $script;
			}
		}


		$script = preg_replace( '~(?<!(?:["\'/]))<!--.*-->(?![\'"/])~Us', '', $script );
		if ( class_exists( '\MatthiasMullie\Minify\JS' ) ) {
			$minify = new \MatthiasMullie\Minify\JS( $script );
			$script = $minify->minify();
		} else {
			$script = rocket_minify_inline_js( $script );
		}
		$script = preg_replace( '~/\*!?\s+.*\*/~sU', '', $script );

		return $script;
	}

	private function update_cache_fragment( $cache_id, $data ) {
		if ( apply_filters( 'rocket_footer_js_save_cache', true ) ) {
			$this->cache_manager->get_store()->update_cache_fragment( $cache_id, $data );
		}
	}

	/**
	 * @param string $src
	 *
	 */
	protected function process_local_script( $src ) {
		if ( 0 == strpos( $src, '/' ) ) {
			$src = $this->home . $src;
		}
		// Remove query strings
		$src_file = $src;
		if ( false !== strpos( $src, '?' ) ) {
			$src_file = substr( $src, 0, strpos( $src, strrchr( $src, '?' ) ) );
		}
		// Break up url
		$url_parts           = parse_url( $src_file );
		$url_parts['host']   = $this->domain;
		$url_parts['scheme'] = is_ssl() ? 'https' : 'http';
		/*
		 * Check and see what version of php-http we have.
		 * 1.x uses procedural functions.
		 * 2.x uses OOP classes with a http namespace.
		 * Convert the address to a path, minify, and add to buffer.
		 */
		if ( class_exists( 'http\Url' ) ) {
			/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
			$url = new \http\Url( $url_parts );
			$url = $url->toString();
		} else {
			$url = http_build_url( $url_parts );
		}


		// Check item cache
		$item_cache_id = [ md5( $url ) ];
		$item_cache    = $this->get_cache_fragment( $item_cache_id );
		// Only run if there is no item cache
		if ( empty( $item_cache ) ) {
			$file          = $this->get_content( str_replace( $this->home, ABSPATH, $url ) );
			$js_part_cache = apply_filters( 'rocket_footer_js_process_local_script', $file, $url );
			$js_part       = $js_part_cache;

			$js_part = $this->minify( $js_part );
			if ( $js_part_cache != $file && apply_filters( 'rocket_footer_js_reprocess_local_script', true, $file, $src ) ) {
				$js_part_cache = $file;
				$js_part_cache = $this->minify( $js_part_cache );
			} else {
				$js_part_cache = $js_part;
			}
			if ( strpos( $js_part, 'sourceMappingURL' ) !== false ) {
				$js_part .= "\n";
			} else {
				$js_part = trim( $js_part );
			}
			$this->js .= $js_part;
			$this->update_cache_fragment( $item_cache_id, $js_part_cache );
		} else {
			$js_part = $item_cache;
			if ( apply_filters( 'rocket_footer_js_reprocess_local_script', true, $js_part, $src ) ) {
				$js_part = apply_filters( 'rocket_footer_js_process_local_script', $js_part, $url );
			}
			$this->js .= $js_part;
		}
	}

	/**
	 * @param $file
	 *
	 * @return bool|string
	 */
	public function get_content( $file ) {
		return $this->get_wp_filesystem()->get_contents( $file );
	}

	/**
	 *
	 */
	protected function process_inline_script() {
		// Check item cache
		$content       = $this->util->maybe_decode_script( $this->dom_collection->current()->textContent );
		$item_cache_id = [ md5( $content ) ];
		$item_cache    = $this->get_cache_fragment( $item_cache_id );
		// Only run if there is no item cache
		if ( empty( $item_cache ) ) {
			// Remove any conditional comments for IE that somehow was put in the script tag
			$js_part = preg_replace( '/(?:<!--)?\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '', $content );
			$js_part = $this->minify( $js_part );
			$this->update_cache_fragment( $item_cache_id, $js_part );
		} else {
			$js_part = $item_cache;
		}
		//Add inline JS to buffer
		$this->js .= $js_part;
		$this->dom_collection->remove();
	}

	/**
	 *
	 */
	protected function build_inline_scripts() {
		$inline_js = '';
		//Combine all inline tags to one
		foreach (
			array_merge(
				iterator_to_array( $this->variable_document->get_script_tags() ),
				iterator_to_array( $this->script_document->get_script_tags() )
			)
			as $tag
		) {
			// If the last character is not a semicolon, and we have content,add one to prevent syntax errors
			if ( 0 < strlen( $inline_js ) && ';' !== $inline_js[ strlen( $inline_js ) - 1 ] ) {
				$inline_js .= ';';
			}
			// Remove any conditional comments for IE that somehow was put in the script tag
			$inline_js .= preg_replace( '/(?:<!--)?\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '', $tag->textContent );
		}
		if ( ! empty( $inline_js ) ) {
			$inline_js = $this->minify( $inline_js );
			$this->insert_inline_script( $inline_js );
		}
	}

	/**
	 * @param $script
	 */
	protected function insert_inline_script( $script ) {
		//Create script tag
		$inline_tag = $this->document->createElement( 'script', $script );
		$inline_tag->setAttribute( 'type', 'text/javascript' );
		// Add element to footer
		$this->body->appendChild( $inline_tag );
	}

	/**
	 * @param $filename
	 *
	 * @return array|string
	 */
	protected function write_cache( $filename ) {
		if ( empty( $this->cache ) ) {
			$data = [ 'filename' => $filename ];
			$this->put_content( $filename, $this->js );
			$data['src'] = get_rocket_cdn_url( set_url_scheme( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $filename ) ), [
				'all',
				'css',
				'js',
				'css_and_js',
			] );
			$this->update_cache_fragment( $this->get_cache_id(), $data );

			return $data;
		}

		return $this->cache;
	}

	/**
	 * @param $file
	 * @param $data
	 *
	 * @return bool
	 */
	public function put_content( $file, $data ) {
		return $this->get_wp_filesystem()->put_contents( $file, $data );
	}

	/**
	 * @param $src
	 */
	protected function add_main_script( $src ) {
		// Create script element
		$external_tag = $this->document->createElement( 'script' );
		$external_tag->setAttribute( 'type', 'text/javascript' );
		$external_tag->setAttribute( 'src', $src );
		$external_tag->setAttribute( 'data-minify', '1' );
		$external_tag->setAttribute( 'async', 'async' );
		$external_tag->setAttribute( 'crossorigin', 'anonymous' );
		// Add element to footer
		$this->body->appendChild( $external_tag );
	}

	/**
	 *
	 */
	protected function fix_old_libxml() {
		// Hack to fix a bug with some libxml versions
		$body       = $this->document->getElementsByTagName( 'body' )->item( 0 );
		$body_class = $body->getAttribute( 'class' );
		if ( empty( $body_class ) ) {
			$body->setAttribute( 'class', implode( ' ', get_body_class() ) );
		}
	}

	/**
	 * @param $buffer
	 *
	 * @return mixed|string
	 */
	protected function do_minify_html( $buffer ) {
		remove_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
		// If HTML minify is on, process it
		if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
			$buffer = rocket_minify_html( $buffer );
			$buffer = preg_replace_callback( '~<WP_ROCKET_FOOTER_JS_LAZYLOAD_START\s*/>(.*)<WP_ROCKET_FOOTER_JS_LAZYLOAD_END\s*/>~s', [
				$this,
				'lazyload_html_callback',
			], $buffer );
			$buffer = preg_replace_callback( '~<WP_ROCKET_FOOTER_JS_LAZYLOAD_START></WP_ROCKET_FOOTER_JS_LAZYLOAD_START>(.*)<WP_ROCKET_FOOTER_JS_LAZYLOAD_END></WP_ROCKET_FOOTER_JS_LAZYLOAD_END>~sU', [
				$this,
				'lazyload_html_callback',
			], $buffer );
		}

		return $buffer;
	}

	/**
	 * @return \DOMDocument
	 */
	public function get_document() {
		return $this->document;
	}

	/**
	 *
	 */
	public function activate() {
		if ( ! $this->get_dependancies_exist() ) {
			return;
		}
		flush_rocket_htaccess();
	}

	/**
	 *
	 */
	public function deactivate() {
		$this->cache_manager->get_store()->delete_cache_branch();
	}

	/**
	 * @return \SplObjectStorage
	 */
	public function get_node_map() {
		return $this->node_map;
	}

	/**
	 * @return DOMCollection|null
	 */
	public function get_dom_collection() {
		return $this->dom_collection;
	}

	/**
	 *
	 */
	public function activate_error_no_wprocket() {
		_e( sprintf( '
	<div class="error notice">
		<p>Opps! %s requires WP-Rocket! Please Download at <a href="http://www.wp-rocket.me">www.wp-rocket.me</a></p>
	</div>', $this->get_plugin_info( 'Name' ) ) );
	}

	/**
	 *
	 */
	public function activate_error_wprocket_inactive() {
		$path = 'wp-rocket/wp-rocket.php';
		_e( sprintf( '
	<div class="error notice">
		<p>Opps! %s requires WP-Rocket! Please Enable the plugin <a href="%s">here</a></p>
	</div>', $this->get_plugin_info( 'Name' ), wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $path ), 'activate-plugin_' . $path ) ) );
	}

	/**
	 *
	 */
	public function activate_error_no_domdocument() {
		_e( sprintf( '
	<div class="error notice">
		<p>Opps! %s requires PHP XML extension! Please contact your web host or system administrator to get this installed.</p>
	</div>', $this->get_plugin_info( 'Name' ) ) );
	}

	/**
	 * @return \Rocket\Footer\JS\DOMDocument
	 */
	public function get_script_document() {
		return $this->script_document;
	}

	/**
	 * @return void
	 */
	public function uninstall() {
		// Noop method
	}

	public function get_transient_prefix() {
		return static::TRANSIENT_PREFIX;
	}

	/**
	 * @return \Rocket\Footer\JS\Util
	 */
	public function get_util() {
		return $this->util;
	}

	/**
	 * @return array
	 */
	public function get_cdn_domains() {
		return $this->cdn_domains;
	}

	/**
	 * @return string
	 */
	public function get_domain() {
		return $this->domain;
	}

	/**
	 * @return \Rocket\Footer\JS\DOMElement
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * @param $url
	 *
	 * @return \http\Url|string
	 */
	public function strip_cdn( $url ) {
		$url_parts           = parse_url( $url );
		$url_parts['host']   = $this->domain;
		$url_parts['scheme'] = is_ssl() ? 'https' : 'http';
		$url                 = http_build_url( $url_parts );

		return $url;
	}

	/**
	 * @param $matches
	 *
	 * @return string
	 */
	protected function lazyload_html_callback( $matches ) {
		return '<!-- ' . html_entity_decode( $matches[1] ) . ' -->';
	}
}

<?php

namespace Rocket\Footer\JS\Cache;

use ComposePress\Core\Abstracts\Component;
use pcfreak30\WordPress\Cache\Store;
use Rocket\Footer\JS;


/**
 * Class Manager
 *
 * @package Rocket\Footer\JS\Cache
 * @property \Rocket\Footer\JS $plugin
 */
class Manager extends Component {
	/**
	 * @var \pcfreak30\WordPress\Cache\Store
	 */
	private $store;

	/**
	 * Manager constructor.
	 *
	 * @param \pcfreak30\WordPress\Cache\Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}

	public function init() {
		add_action( 'after_rocket_clean_domain', [ $this, 'purge_cache' ], 10, 0 );
		add_action( 'after_rocket_clean_domain', [ $this, 'do_preload' ], 10, 0 );
		add_action( 'wp_rocket_start_preload', 'run_rocket_sitemap_preload', 10, 0 );
		add_action( 'after_rocket_clean_post', [ $this, 'purge_post' ] );
		add_action( 'after_rocket_clean_term', [ $this, 'purge_term' ] );
		add_action( 'after_rocket_clean_file', [ $this, 'purge_url' ] );
		$this->store->set_prefix( JS::TRANSIENT_PREFIX );
		$interval = 0;
		if ( function_exists( 'get_rocket_purge_cron_interval' ) ) {
			$interval = get_rocket_purge_cron_interval();
		}
		$this->store->set_expire( apply_filters( 'rocket_footer_js_cache_expire_period', $interval ) );
		$this->store->set_max_branch_length( apply_filters( 'rocket_footer_js_max_branch_length', 50 ) );
	}

	function do_preload() {
		if ( wp_doing_cron() ) {
			run_rocket_sitemap_preload();

			return;
		}
		wp_schedule_single_event( time(), 'wp_rocket_start_preload' );
	}

	/**
	 *
	 */
	public function purge_cache() {
		$this->store->delete_cache_branch();
		rocket_rrmdir( $this->plugin->get_cache_path() );
	}

	/**
	 * @param \WP_Post $post
	 */
	public function purge_post( $post ) {
		$path = [ 'cache', "post_{$post->ID}" ];
		$this->delete_cache_file( $path );
	}

	private function delete_cache_file( $path ) {
		$cache = $this->store->get_cache_fragment( $path );
		if ( ! empty( $cache ) && ! empty( $cache['filename'] ) ) {
			$this->plugin->wp_filesystem->delete( $cache['filename'] );
		}
		$this->store->delete_cache_branch( $path );
	}

	/**
	 * @param \WP_Term $term
	 */
	public function purge_term( $term ) {
		$path = [ 'cache', "term_{$term->term_id}" ];
		$this->delete_cache_file( $path );
	}

	/**
	 * @param string $url
	 */
	public function purge_url( $url ) {
		$url  = md5( $url );
		$path = [ 'cache', "url_{$url}" ];
		$this->delete_cache_file( $path );
	}

	public function clear_minify_url( $url ) {
		$key = [ md5( $url ) ];

		return $this->get_store()->delete_cache_branch( $key );
	}

	/**
	 * @return \pcfreak30\WordPress\Cache\Store
	 */
	public function get_store() {
		return $this->store;
	}
}

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
		add_action( 'wp_rocket_start_preload', 'run_rocket_sitemap_preload', 10, 0 );
		add_action( 'after_rocket_clean_post', [ $this, 'purge_post' ] );
		add_action( 'after_rocket_clean_term', [ $this, 'purge_term' ] );
		add_action( 'after_rocket_clean_file', [ $this, 'purge_url' ] );
		add_action( 'wp_rocket_start_preload', 'run_rocket_sitemap_preload', 10, 0 );
		add_action( 'rocket_footer_js_purge_cache', [ $this, 'do_purge_cache' ] );
		if ( is_admin() && $this->get_admin_cache_flag() ) {
			add_action( 'admin_notices', [ $this, 'cache_purge_notice' ] );
		}
		$this->store->set_prefix( JS::TRANSIENT_PREFIX );
		$interval = 0;
		if ( function_exists( 'get_rocket_purge_cron_interval' ) ) {
			$interval = get_rocket_purge_cron_interval();
		}
		$this->store->set_expire( apply_filters( 'rocket_footer_js_cache_expire_period', $interval ) );
		$this->store->set_max_branch_length( apply_filters( 'rocket_footer_js_max_branch_length', 50 ) );
	}

	private function get_admin_cache_flag() {
		return get_transient( $this->get_admin_cache_flag_name() );
	}

	private function get_admin_cache_flag_name() {
		return "{$this->plugin->get_safe_slug()}_cache_purging";
	}

	public function purge_cache() {
		if ( $this->get_admin_cache_flag() && ! wp_next_scheduled( 'rocket_footer_js_purge_cache' ) ) {
			$this->clear_admin_cache_flag();
		}
		if ( $this->get_admin_cache_flag() ) {
			return;
		}
		$size = $this->store->get_cache_fragment( [ 'count' ] );

		if ( $size > apply_filters( 'rocket_footer_js_background_cache_purge_item_threshold', 25, $size ) && ! wp_doing_cron() ) {
			wp_schedule_single_event( time(), 'rocket_footer_js_purge_cache' );
			$this->set_admin_cache_flag();

			return;
		}
		$this->do_purge_cache();
	}

	private function clear_admin_cache_flag() {
		delete_transient( "{$this->plugin->get_safe_slug()}_cache_purging" );
	}

	private function set_admin_cache_flag() {
		set_transient( $this->get_admin_cache_flag_name(), true, DAY_IN_SECONDS );
	}

	public function do_purge_cache() {
		$this->set_admin_cache_flag();
		$this->store->delete_cache_branch();
		$this->delete_minify_files();
		$this->clear_admin_cache_flag();
		remove_action( 'after_rocket_clean_domain', [ $this, 'purge_cache' ] );
		rocket_clean_domain();
		$this->do_preload();
	}

	private function delete_minify_files() {
		rocket_rrmdir( $this->plugin->get_cache_path() );
	}

	function do_preload() {
		if ( wp_doing_cron() ) {
			run_rocket_sitemap_preload();

			return;
		}
		wp_schedule_single_event( time(), 'wp_rocket_start_preload' );
	}

	/**
	 * @param \WP_Post $post
	 */
	public function purge_post( $post ) {
		$path = [ 'cache', "post_{$post->ID}" ];
		$this->delete_cache_file( $path );
	}

	private function delete_cache_file( $path ) {
		$cache = $this->store->get_cache_fragment( array_merge( $path, [ 'cache_1' ] ) );
		if ( ! empty( $cache ) ) {
			foreach ( $cache as $item ) {
				$item = explode( '_', $item );
				$item = end( $item );
				$item = $this->store->get_cache_fragment( array_merge( $path, [ $item ] ) );
				if ( ! empty( $item['filename'] ) ) {
					$this->plugin->wp_filesystem->delete( $item['filename'] );
				}
			}
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

	public function cache_purge_notice() {
		$class   = 'notice notice-info';
		$message = __( sprintf( '%s is currently purging the JS minify cache in the background', $this->plugin->get_plugin_info( 'Name' ) ), $this->plugin->get_safe_slug() );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}

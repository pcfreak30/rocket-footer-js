<?php


namespace Rocket\Footer\JS;


class Request extends ComponentAbstract {
	public function init() {
		global $pagenow;
		parent::init();
		add_action( 'init', [ $this, 'init_action' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		if ( ! is_admin() ) {
			add_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
			add_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
			if ( ! in_array( $pagenow, array( 'wp-login.php', 'wp-signup.php' ) ) ) {
				// Ensure zxcvbn is loaded normally, not async so it gets minified
				add_action( 'wp_default_scripts', [ $this, 'deasync_zxcvbn' ] );
			}
		}
		add_filter( 'pre_get_rocket_option_minify_js_combine_all', '__return_zero' );
		add_filter( 'pre_get_rocket_option_defer_all_js', '__return_zero' );
		add_filter( 'pre_get_rocket_option_deferred_js_files', '__return_zero' );
		add_filter( 'rocket_buffer', [ $this->app, 'process_buffer' ], PHP_INT_MAX );
		remove_filter( 'rocket_buffer', 'rocket_insert_deferred_js', 11 );
		remove_filter( 'rocket_buffer', 'rocket_defer_js', 14 );
	}

	public function init_action() {
		if ( function_exists( 'get_rocket_option' ) && ! get_rocket_option( 'emoji', 0 ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			add_action( 'wp_footer', 'print_emoji_detection_script' );
		}
		if ( $this->app->get_lazyload_manager()->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', 'rocket_footer_js_scripts' );
		}
	}

	public function enqueue_scripts() {
		global $a3_lazy_load_global_settings;
		$lazy_load = false;
		if ( ! empty( $a3_lazy_load_global_settings ) ) {
			wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', $this->app->get_plugin_file() ), array( 'jquery-lazyloadxt' ) );
			$lazy_load = true;
		}
		if ( ! $lazy_load ) {
			wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', $this->app->get_plugin_file() ), array( 'lazy-load-xt-script' ) );
		}
	}

	/**
	 * @param \WP_Scripts $scripts
	 */
	public function deasync_zxcvbn( $scripts ) {
		if ( ! empty( $scripts->registered['zxcvbn-async'] ) ) {
			$scripts->registered['zxcvbn-async']->src   = includes_url( '/js/zxcvbn.min.js' );
			$scripts->registered['zxcvbn-async']->extra = array();
		}
	}
}
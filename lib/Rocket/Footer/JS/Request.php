<?php


namespace Rocket\Footer\JS;

use pcfreak30\WordPress\Plugin\Framework\ComponentAbstract;

class Request extends ComponentAbstract {
	public function init() {
		add_action( 'init', [ $this, 'init_action' ] );
		if ( ! is_admin() ) {
			if ( is_plugin_active( 'rocket-async-css/rocket-async-css.php' ) ) {
				remove_filter( 'rocket_buffer', 'rocket_minify_process', 13 );
				remove_filter( 'rocket_buffer', 'rocket_minify_html', 20 );
			} else {
				add_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
				add_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
			}
			if ( ! in_array( $this->pagenow, array( 'wp-login.php', 'wp-signup.php' ) ) ) {
				// Ensure zxcvbn is loaded normally, not async so it gets minified
				add_action( 'wp_default_scripts', [ $this, 'deasync_zxcvbn' ] );
			}
		}
		add_filter( 'pre_get_rocket_option_minify_js_combine_all', '__return_zero' );
		add_filter( 'pre_get_rocket_option_defer_all_js', '__return_zero' );
		add_filter( 'pre_get_rocket_option_deferred_js_files', '__return_zero' );
		add_filter( 'rocket_buffer', [ $this->plugin, 'process_buffer' ], PHP_INT_MAX );
		add_filter( 'rocket_htaccess_web_fonts_access', [ $this, 'add_js_to_htaccess_cors' ] );
		remove_filter( 'rocket_buffer', 'rocket_insert_deferred_js', 11 );
		remove_filter( 'rocket_buffer', 'rocket_defer_js', 14 );
	}

	public function init_action() {
		if ( function_exists( 'get_rocket_option' ) && ! get_rocket_option( 'emoji', 0 ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			add_action( 'wp_footer', 'print_emoji_detection_script' );
		}
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
	}

	public function enqueue_scripts() {
		global $a3_lazy_load_global_settings;
		$dep = 'lazy-load-xt-script';
		if ( ! empty( $a3_lazy_load_global_settings ) ) {
			$dep = 'jquery-lazyloadxt';
		}

		if ( wp_script_is( $dep, 'registered' ) ) {
			wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', $this->plugin->get_plugin_file() ), array( $dep ) );
			wp_enqueue_script( 'jquery-lazyloadxt.videoembed', plugins_url( 'assets/js/jquery.lazyloadxt.videoembed.js', $this->plugin->get_plugin_file() ), array( $dep ) );
			wp_enqueue_script( 'jquery-lazyloadxt.bg', plugins_url( 'assets/js/jquery.lazyloadxt.bg.js', $this->plugin->get_plugin_file() ), array( $dep ) );
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

	public function add_js_to_htaccess_cors( $rules ) {
		$rules    = explode( "\n", $rules );
		$rules[4] = str_replace( ')$', '|js)$', $rules[4] );
		$rules    = implode( "\n", $rules );

		return $rules;
	}
}
<?php


namespace Rocket\Footer\JS;

use ComposePress\Core\Abstracts\Component;

class Request extends Component {
	public function init() {
		add_action( 'init', [ $this, 'init_action' ] );
		if ( ! is_admin() ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
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
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				add_filter( 'pre_get_rocket_option_lazyload', '__return_zero' );
				add_filter( 'pre_get_rocket_option_lazyload_iframes', '__return_zero' );
			}
		}
		add_filter( 'pre_get_rocket_option_minify_js_combine_all', '__return_zero' );
		add_filter( 'pre_get_rocket_option_defer_all_js', '__return_zero' );
		add_filter( 'pre_get_rocket_option_deferred_js_files', '__return_zero' );
		add_filter( 'rocket_buffer', [ $this->plugin, 'process_buffer' ], 9999 );
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
			/** @var \_WP_Dependency $script */
			$script = wp_scripts()->registered[ $dep ];
			wp_deregister_script( $dep );
			wp_enqueue_script( $dep, plugins_url( 'assets/js/jquery.lazyloadxt.js', $this->plugin->get_plugin_file(), $script->deps ) );
			foreach ( $script->extra as $key => $data ) {
				wp_script_add_data( $dep, $key, $data );
			}
			wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			wp_enqueue_script( 'jquery-lazyloadxt.videoembed', plugins_url( 'assets/js/jquery.lazyloadxt.videoembed.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			wp_enqueue_script( 'jquery-lazyloadxt.video', plugins_url( 'assets/js/jquery.lazyloadxt.video.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			wp_enqueue_script( 'jquery-lazyloadxt.bg', plugins_url( 'assets/js/jquery.lazyloadxt.bg.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			wp_enqueue_script( 'jquery.lazyloadxt.imagefixes', plugins_url( 'assets/js/jquery.lazyloadxt.imagefixes.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			wp_enqueue_style( 'rocket-footer-js-video-lazyload', plugins_url( 'assets/css/video-lazyload.css', $this->plugin->get_plugin_file() ) );
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
		$rules = explode( "\n", $rules );
		$match = false;
		foreach ( $rules as $index => $rule ) {
			if ( false !== stripos( $rule, 'FilesMatch' ) ) {
				$match = $index;
				break;
			}
		}
		if ( $match ) {
			$rules[ $match ] = str_replace( ')$', '|js)$', $rules[ $match ] );
		}

		$rules = implode( "\n", $rules );

		return $rules;
	}
}
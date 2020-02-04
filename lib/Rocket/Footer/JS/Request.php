<?php


namespace Rocket\Footer\JS;

use ComposePress\Core\Abstracts\Component;

class Request extends Component {
	public function init() {
		add_action( 'init', [ $this, 'init_action' ] );
		if ( ! is_admin() && ! wp_is_json_request() ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_plugin_active( 'rocket-async-css/rocket-async-css.php' ) ) {
				remove_filter( 'rocket_buffer', 'rocket_minify_process', 13 );
				remove_filter( 'rocket_buffer', 'rocket_minify_html', 20 );
			}
			add_filter( 'pre_get_rocket_option_minify_js', '__return_zero' );
			add_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );

			if ( ! in_array( $this->pagenow, array( 'wp-login.php', 'wp-signup.php' ) ) ) {
				// Ensure zxcvbn is loaded normally, not async so it gets minified
				add_action( 'wp_default_scripts', [ $this, 'deasync_zxcvbn' ] );
			}
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				add_filter( 'pre_get_rocket_option_lazyload', '__return_zero' );
				add_filter( 'pre_get_rocket_option_lazyload_iframes', '__return_zero' );
			}
			add_filter( 'pre_get_rocket_option_minify_html', '__return_zero' );
			if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
				add_filter( 'get_avatar_url', 'rocket_cdn_file' );
			}
		}
		add_filter( 'pre_get_rocket_option_minify_js_combine_all', '__return_zero' );
		add_filter( 'pre_get_rocket_option_defer_all_js', '__return_zero' );
		add_filter( 'pre_get_rocket_option_deferred_js_files', '__return_zero' );
		add_filter( 'rocket_buffer', [ $this->plugin, 'process_buffer' ], 9999 );
		add_filter( 'rocket_htaccess_web_fonts_access', [ $this, 'add_js_to_htaccess_cors' ] );
		remove_filter( 'rocket_buffer', 'rocket_insert_deferred_js', 11 );
		remove_filter( 'rocket_buffer', 'rocket_defer_js', 14 );
		add_action( 'save_post', 'rocket_clean_post' );
		add_action( 'shutdown', [ $this, 'maybe_ajax_spoof' ], - 1 );
		add_filter( 'rocket_lazyload_script_tag', [ $this, 'strip_no_minify' ] );
	}

	public function strip_no_minify( $script ) {
		return str_replace( 'data-no-minify="1"', '', $script );
	}

	public function maybe_ajax_spoof() {
		if ( did_action( 'after_rocket_clean_post' ) || did_action( 'after_rocket_clean_term' ) ) {
			add_filter( 'wp_doing_ajax', '__return_false' );
			add_action( 'shutdown', [ $this, 'remove_ajax_spoof' ], 1 );
		}
	}

	public function remove_ajax_spoof() {
		add_filter( 'wp_doing_ajax', '__return_false' );
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

		$suffix = ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'min.' : '';

		if ( wp_script_is( $dep, 'registered' ) ) {
			wp_deregister_style( 'jquery-lazyloadxt-fadein-css' );
			wp_deregister_style( 'jquery-lazyloadxt-spinner-css' );

			/** @var \_WP_Dependency $script */
			$script = wp_scripts()->registered[ $dep ];
			wp_deregister_script( $dep );
			wp_enqueue_script( 'rocket-footer-js-video-mutation-observer-polyfill', plugins_url( "assets/js/polyfill/mutation-observer.{$suffix}js", $this->plugin->get_plugin_file() ) );
			wp_enqueue_script( 'rocket-footer-js-video-intersection-observer-polyfill', plugins_url( "assets/js/polyfill/intersection-observer.{$suffix}js", $this->plugin->get_plugin_file() ) );
			wp_enqueue_script( 'rocket-footer-js-custom-event-polyfill', plugins_url( "assets/js/polyfill/custom-event.{$suffix}js", $this->plugin->get_plugin_file() ) );
			wp_enqueue_script( 'jquery-lazyloadxt-dummy', plugins_url( 'assets/js/lazysizes.lazyloadxt.compat.js', $this->plugin->get_plugin_file(), [ 'jquery' ] ) );
			foreach ( $script->extra as $key => $data ) {
				wp_script_add_data( $dep, $key, $data );
			}
			$dep           = 'jquery-lazyloadxt-dummy';
			$lazysize_deps = [
				'rocket-footer-js-video-mutation-observer-polyfill',
				'rocket-footer-js-video-intersection-observer-polyfill',
				'rocket-footer-js-custom-event-polyfill',
			];

			if ( ! is_plugin_active( 'rocket-async-css/rocket-async-css.php' ) ) {
				wp_enqueue_script( 'rocket-footer-js-picturefill-polyfill', plugins_url( 'assets/js/polyfill/picturefill.min.js', $this->plugin->get_plugin_file() ) );
				$lazysize_deps[] = 'rocket-footer-js-picturefill-polyfill';
			}

			wp_enqueue_script( 'rocket-footer-js-lazysizes', plugins_url( "assets/js/lazysizes.{$suffix}js", $this->plugin->get_plugin_file(), $lazysize_deps ) );
			if ( apply_filters( 'rocket_footer_js_load_script_lazy_load_widgets', true ) ) {
				wp_enqueue_script( 'jquery-lazyloadxt.widget', plugins_url( 'assets/js/jquery.lazyloadxt.widget.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			}
			if ( apply_filters( 'rocket_footer_js_load_script_lazy_load_video_embed', true ) ) {
				wp_enqueue_script( 'jquery-lazyloadxt.videoembed', plugins_url( 'assets/js/jquery.lazyloadxt.videoembed.js', $this->plugin->get_plugin_file() ), [ $dep ] );
				wp_enqueue_style( 'rocket-footer-js-video-lazyload', plugins_url( 'assets/css/video-lazyload.css', $this->plugin->get_plugin_file() ) );
			}
			if ( apply_filters( 'rocket_footer_js_load_script_lazy_load_video', true ) ) {
				wp_enqueue_script( 'jquery-lazyloadxt.video', plugins_url( 'assets/js/jquery.lazyloadxt.video.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			}
			if ( apply_filters( 'rocket_footer_js_load_script_lazy_load_bg', true ) ) {
				wp_enqueue_script( 'jquery-lazyloadxt.bg', plugins_url( 'assets/js/jquery.lazyloadxt.bg.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			}
			if ( apply_filters( 'rocket_footer_js_load_script_image_hacks', false ) ) {
				wp_enqueue_script( 'jquery.lazyloadxt.imagefixes', plugins_url( 'assets/js/jquery.lazyloadxt.imagefixes.js', $this->plugin->get_plugin_file() ), [ $dep ] );
			}
			wp_enqueue_style( 'rocket-footer-js-lazyload', plugins_url( 'assets/css/lazyload.css', $this->plugin->get_plugin_file() ) );

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

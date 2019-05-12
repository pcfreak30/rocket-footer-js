<?php


namespace Rocket\Footer\JS\Integration;


class A3LazyLoad extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( $this->plugin->lazyload_manager->is_enabled() && class_exists( 'A3_Lazy_Load' ) ) {
			if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
				add_filter( 'a3_lazy_load_images_before', 'rocket_cdn_images' );
			}
			if ( is_user_logged_in() && ! ( 0 < get_rocket_option( 'cache_logged_user' ) ) && ! apply_filters( 'rocket_footer_js_lazy_load_members_override', false ) ) {
				add_filter( 'a3_lazy_load_run_filter', '__return_false' );
			}
			add_filter( 'a3_lazy_load_videos_before', [ $this, 'lazyload_mediaelement_videos' ] );
			add_filter( 'a3_lazy_load_videos_after', [ $this, 'remove_hidden_class' ] );
			add_filter( 'a3_lazy_load_images_after', [ $this, 'remove_hidden_class' ] );
			add_filter( 'a3_lazy_load_images_after', [ $this, 'remove_dummy_src' ] );
		}
	}

	public function lazyload_mediaelement_videos( $html ) {
		$html = str_replace( [
			'wp-audio-shortcode',
			'wp-video-shortcode',
			'wp-video-shortcode-lazyload-lazyload',
			'wp-audio-shortcode-lazyload-lazyload',
		], [
			'wp-audio-shortcode-lazyload',
			'wp-video-shortcode-lazyload',
			'wp-audio-shortcode-lazyload',
			'wp-video-shortcode-lazyload',
		], $html );

		return $html;
	}

	public function remove_hidden_class( $html ) {
		return str_replace( 'lazy lazy-hidden', 'lazyload', $html );
	}

	public function remove_dummy_src( $html ) {
		return str_replace( 'src="' . A3_LAZY_LOAD_IMAGES_URL . '/lazy_placeholder.gif"', '', $html );
	}
}

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
			if ( ! is_admin() && is_user_logged_in() && ! ( 0 < get_rocket_option( 'cache_logged_user' ) ) && ! apply_filters( 'rocket_footer_js_lazy_load_members_override', false ) ) {
				add_filter( 'a3_lazy_load_run_filter', '__return_false' );
			}
			add_filter( 'a3_lazy_load_videos_before', [ $this, 'lazyload_mediaelement_videos' ] );
			add_filter( 'a3_lazy_load_videos_after', [ $this, 'remove_hidden_class' ] );
			add_filter( 'a3_lazy_load_images_after', [ $this, 'remove_hidden_class' ] );
			add_filter( 'a3_lazy_load_images_after', [ $this, 'remove_dummy_src' ] );
			add_filter( 'a3_lazy_load_images_after', [ $this, 'remove_duplicate_srcset' ] );
			add_filter( 'rocket_cdn_images_html', [ $this, 'fix_fake_src' ] );
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
		$html = str_replace( 'lazy lazy-hidden', 'lazyload', $html );
		$html = str_replace( 'lazyload lazyload', 'lazyload', $html );
		$html = str_replace( 'data-lazy-type="image"', '', $html );

		return $html;
	}

	public function remove_dummy_src( $html ) {
		$placeholder     = A3_LAZY_LOAD_IMAGES_URL . '/lazy_placeholder.gif';
		$placeholder_cdn = get_rocket_cdn_url( A3_LAZY_LOAD_IMAGES_URL . '/lazy_placeholder.gif', [ 'images' ], $placeholder );

		$src_string      = ' src="%s"';
		$fake_src_string = ' data-fake-src="%s"';

		$html = str_replace( sprintf( $src_string, $placeholder ), sprintf( $fake_src_string, $placeholder ), $html );
		$html = str_replace( sprintf( $src_string, $placeholder_cdn ), sprintf( $fake_src_string, $placeholder_cdn ), $html );

		return $html;
	}

	public function fix_fake_src( $html ) {
		return str_replace( 'data-fake- src', 'data-fake-src', $html );
	}

	public function remove_duplicate_srcset( $html ) {
		return str_replace( 'data-srcset="" data-srcset=', 'data-srcset=', $html );
	}

}

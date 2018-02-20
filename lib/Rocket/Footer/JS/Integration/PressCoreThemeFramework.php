<?php


namespace Rocket\Footer\JS\Integration;


class PressCoreThemeFramework extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		/** @var \WP_Theme $theme */
		$theme = wp_get_theme()->parent() ? wp_get_theme()->parent() : wp_get_theme();
		if ( 'dream-theme' === strtolower( wp_strip_all_tags( $theme->author ) ) ) {
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				require_once trailingslashit( dirname( $this->plugin->plugin_file ) ) . trailingslashit( 'lib' ) . trailingslashit( 'overrides' ) . trailingslashit( 'presscore-theme-framework' ) . 'lazy-load.php';
				add_action( 'rocket_footer_js_do_lazyload', [ $this, 'process' ], 10, 2 );
				add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			}
			if ( function_exists( 'rocket_cdn_images' ) ) {
				add_filter( 'dt_get_thumb_img-args', [ $this, 'remove_cdn_hooks' ] );
				add_filter( 'dt_get_thumb_img-output', [ $this, 'add_cdn_hooks_process_html' ], PHP_INT_MAX );
			}
		}
	}

	public function process( $document = null, $content_document = null ) {
		if ( ! $document ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$document = $this->plugin->document;
		}
		if ( ! $content_document ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$content_document = $document;
		}
		$xpath = new \DOMXPath( $content_document );
		/** @var \Rocket\Footer\JS\DOMElement $tag */
		foreach ( $xpath->query( '//img[contains(concat(" ", normalize-space(@class), " "), " preload-me ")]' ) as $tag ) {
			$class = $tag->getAttribute( 'class' );
			$class = trim( str_replace( 'preload-me', '', $class ) );
			$tag->setAttribute( 'class', $class );
		}
	}

	public function remove_cdn_hooks( $opts ) {
		remove_filter( 'wp_get_attachment_image_src', 'rocket_cdn_attachment_image_src', PHP_INT_MAX );
		remove_filter( 'wp_get_attachment_url', 'rocket_cdn_file', PHP_INT_MAX );

		if ( ! empty( $opts['img_meta'] ) ) {
			$opts['img_meta'][0] = $this->plugin->strip_cdn( $opts['img_meta'][0] );
		}

		return $opts;
	}

	public function add_cdn_hooks_process_html( $html ) {
		add_filter( 'wp_get_attachment_image_src', 'rocket_cdn_attachment_image_src', PHP_INT_MAX );
		add_filter( 'wp_get_attachment_url', 'rocket_cdn_file', PHP_INT_MAX );

		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
		add_filter( 'rocket_cdn_images_html', [ $this, 'add_cdn_srcset' ] );

		$html = rocket_cdn_images( $html );
		remove_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
		remove_filter( 'rocket_cdn_images_html', [ $this, 'add_cdn_srcset' ] );

		return $html;
	}

	public function add_cdn_srcset( $html ) {
		if ( preg_match_all( '/(data-srcset|srcset)=[\'"]?([^\'">]+)[\'"]/i', $html, $attr_match ) ) {
			foreach ( $attr_match[2] as $k => $image_url ) {
				$url      = get_rocket_cdn_url( $image_url, [ 'all', 'images' ] );
				$new_attr = str_replace( $image_url, $url, $attr_match[0][ $k ] );
				$html     = str_replace( $attr_match[0][ $k ], $new_attr, $html );
			}
		}

		return $html;
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', '(function($){$(function(){$("img").on("lazyload",function(){$(this).closest(".iso-grid, .iso-container").isotope("layout")})})})(jQuery);' );
	}
}
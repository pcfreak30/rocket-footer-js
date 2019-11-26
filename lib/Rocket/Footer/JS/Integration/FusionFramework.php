<?php


namespace Rocket\Footer\JS\Integration;


class FusionFramework extends IntegrationAbstract {

	private $fusion_images_filter_priority;

	public function init() {
		if ( class_exists( 'Avada' ) || class_exists( 'FusionCore_Plugin' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				add_filter( 'the_content', [ $this, 'privacy_lazyload' ], 100000 );
				add_filter( 'privacy_iframe_embed', [ $this, 'privacy_lazyload' ], 21 );
				add_filter( 'a3_lazy_load_videos_after', [ $this, 'privacy_lazyload' ] );
				add_filter( 'wp_enqueue_scripts', [ $this, 'remove_lazysizes' ], 11 );
				add_filter( 'avada_setting_get_lazy_load', '__return_zero' );
			}
			if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
				foreach (
					[
						'favicon[url]',
						'iphone_icon[url]',
						'iphone_icon_retina[url]',
						'ipad_icon[url]',
						'ipad_icon_retina[url]',
						'mobile_logo[url]',
						'mobile_logo_retina[url]',
						'sticky_header_logo[url]',
						'sticky_header_logo_retina[url]',
					] as $setting
				) {
					add_filter( "avada_setting_get_{$setting}", 'rocket_cdn_file' );

					$setting = explode( '[', $setting );
					$setting = $setting[0];
					add_filter( "avada_setting_get_{$setting}", [ $this, 'cdnify_logo' ] );
				}
				add_filter( 'after_setup_theme', [ $this, 'setup_opengraph_cdn' ] );
			}
			add_action( 'rocket_footer_js_lazyload_video_before_calculate_srcset', [
				$this,
				'remove_fusion_image_srcset_filter',
			] );
			add_action( 'rocket_footer_js_lazyload_video_after_calculate_srcset', [
				$this,
				'add_fusion_image_srcset_filter',
			] );
			add_filter( 'rocket_footer_js_load_script_image_hacks', '__return_true' );
		}
	}

	public function cdnify_logo( $setting ) {
		$setting['url'] = rocket_cdn_file( $setting['url'] );

		if ( isset( $setting['thumbnail'] ) ) {
			$setting['thumbnail'] = rocket_cdn_file( $setting['thumbnail'] );
		}

		return $setting;
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', '(function($){$(function(){var selector=".iso-grid, .iso-container, .fusion-gallery, .fusion-portfolio-wrapper";$("img").on("lazyload",function(){if(!$.fn.isotope)return;var isotope=$(this).closest(selector);if(isotope.data("isotope"))isotope.isotope("layout")});$(".fusion-portfolio-wrapper article").one("transitionend",function(){var isotope=$(this).closest(selector);if(isotope.data("isotope"))isotope.isotope("layout")})})})(jQuery);' );
	}

	public function privacy_lazyload( $html ) {
		preg_match_all( '#<iframe(.*?)></iframe>#is', $html, $matches );
		if ( empty( $matches[0] ) ) {
			return $html;
		}
		$replacements = [];
		foreach ( $matches[0] as $iframeHTML ) {
			if ( false === strpos( $iframeHTML, ' data-privacy-src=' ) ) {
				$replacements[] = $iframeHTML;
				continue;
			}
			if ( false !== strpos( $iframeHTML, ' data-src=""' ) ) {
				$iframeHTML = str_replace( ' data-src=""', '', $iframeHTML );
			}
			if ( strpos( $iframeHTML, ' data-src=' ) === false ) {
				$iframeHTML = preg_replace( '/ src="[^"]"/U', '', $iframeHTML );
			}

			$iframeHTML     = str_replace( ' src=""', '', $iframeHTML );
			$iframeHTML     = preg_replace( '/ data-privacy-src="([^"]+)"/U', ' data-privacy-src-disabled data-src="$1"', $iframeHTML );
			$replacements[] = $iframeHTML;
		}

		return str_replace( $matches[0], $replacements, $html );
	}

	public function setup_opengraph_cdn() {
		if ( class_exists( 'Avada' ) ) {
			add_filter( 'option_' . \Avada_Settings::get_option_name(), [ $this, 'opengraph_cdn' ] );
		} else if ( class_exists( 'FusionCore_Plugin' ) ) {
			add_filter( 'option_' . \Fusion_Settings::get_option_name(), [ $this, 'opengraph_cdn' ] );
		}
	}

	public function opengraph_cdn( $settings ) {
		$settings['logo']['url'] = get_rocket_cdn_url( $settings['logo']['url'] );

		return $settings;
	}


	public function remove_fusion_image_srcset_filter() {
		$fusion_images                       = Avada()->fusion_library->images;
		$this->fusion_images_filter_priority = has_filter( 'wp_calculate_image_srcset', [
			$fusion_images,
			'set_largest_image_size',
		] );
		if ( $this->fusion_images_filter_priority ) {
			remove_filter( 'wp_calculate_image_srcset', [ $fusion_images, 'set_largest_image_size' ] );
		}
	}

	public function add_fusion_image_srcset_filter() {
		$fusion_images = Avada()->fusion_library->images;
		if ( $this->fusion_images_filter_priority ) {
			add_filter( 'wp_calculate_image_srcset', [
				$fusion_images,
				'set_largest_image_size',
			], $this->fusion_images_filter_priority, 5 );
		}
	}

	public function remove_lazysizes() {
		wp_dequeue_script( 'lazysizes' );
	}
}

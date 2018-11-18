<?php


namespace Rocket\Footer\JS\Integration;


class FusionFramework extends IntegrationAbstract {
	public function init() {
		if ( class_exists( 'Avada' ) || class_exists( 'FusionCore_Plugin' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				add_filter( 'the_content', [ $this, 'privacy_lazyload' ], 100000 );
				add_filter( 'privacy_iframe_embed', [ $this, 'privacy_lazyload' ], 21 );
				add_filter( 'a3_lazy_load_videos_after', [ $this, 'privacy_lazyload' ] );

			}
			if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
				foreach (
					[
						'favicon[url]',
						'iphone_icon[url]',
						'iphone_icon_retina[url]',
						'ipad_icon[url]',
						'ipad_icon_retina[url]',
					] as $setting
				) {
					add_filter( "avada_setting_get_{$setting}", 'rocket_cdn_file' );
				}
				add_filter( 'after_setup_theme', [ $this, 'setup_opengraph_cdn' ] );
			}
		}
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', '(function($){$(function(){$("img").on("lazyload",function(){if(!$.fn.isotope){return;}$(this).closest(".iso-grid, .iso-container, .fusion-gallery").isotope("layout")})})})(jQuery);' );
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
}

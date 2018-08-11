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
}
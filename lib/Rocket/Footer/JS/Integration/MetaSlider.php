<?php


namespace Rocket\Footer\JS\Integration;


class MetaSlider extends IntegrationAbstract {
	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\MetaSliderPlugin' ) ) {
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				$settings                               = $this->a3_lazy_load_global_settings;
				$settings['a3l_image_include_noscript'] = false;
				$this->a3_lazy_load_global_settings     = $settings;
				add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			}
			add_filter( 'metaslider_image_slide_attributes', [ $this, 'process_webp' ] );
		}
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', 'jQuery(function($){$(".nivoSlider").find("img").on("lazyload",function(){$(window).trigger("resize")});jQuery("img").filter(function(){return!jQuery(this).parent().hasClass("nivoSlider")}).on("lazyload",function(){$(".nivoSlider img").show().css("visibility","visible")})});' );
	}

	public function process_webp( $attributes ) {
		if ( isset( $attributes['src'] ) ) {
			$attributes['src']   = apply_filters( 'rocket_footer_js_webp_process_url', $attributes['src'] );
			$attributes['thumb'] = $attributes['src'];
		}

		return $attributes;
	}
}

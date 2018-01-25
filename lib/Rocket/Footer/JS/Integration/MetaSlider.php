<?php


namespace Rocket\Footer\JS\Integration;


class MetaSlider extends IntegrationAbstract {
	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\MetaSliderPlugin' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			$settings                               = $this->a3_lazy_load_global_settings;
			$settings['a3l_image_include_noscript'] = false;
			$this->a3_lazy_load_global_settings     = $settings;
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		}
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', 'jQuery(function($){$(".nivoSlider").find("img").on("lazyload",function(){$(window).trigger("resize")});jQuery("img").filter(function(){return!jQuery(this).parent().hasClass("nivoSlider")}).on("lazyload",function(){$(".nivoSlider img").show().css("visibility","visible")})});' );
	}
}
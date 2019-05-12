<?php


namespace Rocket\Footer\JS\Integration;


class MasterSlider extends IntegrationAbstract {
	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\Master_Slider' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			$settings                               = $this->a3_lazy_load_global_settings;
			$settings['a3l_image_include_noscript'] = false;
			$this->a3_lazy_load_global_settings     = $settings;
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 16 );
		}
	}

	public function scripts() {
		wp_add_inline_script( 'masterslider-core', '(function($){$(window).load(function(){$.each(window.masterslider_instances,function(index,instance){var cb=function(){$(window).lazyLoadXT();if(0===instance.slideController.currentSlide.$bg_img.data("lazied"))instance.slideController.addEventListener(MSSliderEvent.CHANGE_START,cb,instance);instance.slideController.addEventListener(MSSliderEvent.CHANGE_END,cb,instance)})})})(jQuery);' );
	}
}

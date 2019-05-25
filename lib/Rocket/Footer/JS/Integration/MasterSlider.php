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
		}
	}
}

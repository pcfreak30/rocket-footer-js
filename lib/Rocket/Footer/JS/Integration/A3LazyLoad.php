<?php


namespace Rocket\Footer\JS\Integration;


use WP\CriticalCSS\ComponentAbstract;

class A3LazyLoad extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( $this->plugin->lazyload_manager->is_enabled() && 0 < (int) get_rocket_option( 'cdn' ) ) {
			add_filter( 'a3_lazy_load_images_before', 'rocket_cdn_images' );
		}
	}
}
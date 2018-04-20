<?php


namespace Rocket\Footer\JS\Integration;


use WP\CriticalCSS\ComponentAbstract;

class A3LazyLoad extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( $this->plugin->lazyload_manager->is_enabled() ) {
			if ( 0 < (int) get_rocket_option( 'cdn' ) ) {
				add_filter( 'a3_lazy_load_images_before', 'rocket_cdn_images' );
			}
			if ( is_user_logged_in() && ! ( 0 < get_rocket_option( 'cache_logged_user' ) ) && ! apply_filters( 'rocket_footer_js_lazy_load_members_override', false ) ) {
				add_filter( 'a3_lazy_load_run_filter', '__return_false' );
			}
		}
	}
}
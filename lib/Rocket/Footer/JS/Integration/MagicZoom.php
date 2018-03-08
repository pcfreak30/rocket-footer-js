<?php


namespace Rocket\Footer\JS\Integration;


class MagicZoom extends IntegrationAbstract {
	public function init() {
		if ( function_exists( 'magictoolbox_WooCommerce_MagicZoom_init' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_action( 'wp_footer', [ $this, 'scripts' ], 16 );
		}
	}

	public function scripts() {
		if ( wp_script_is( 'magictoolbox_magiczoom_script' ) ) {
			wp_add_inline_script( 'magictoolbox_magiczoom_script', '(function($){$(window).on("load", function(){if(window.MagicRefresh){MagicRefresh();}})})(jQuery);' );
		}
	}
}
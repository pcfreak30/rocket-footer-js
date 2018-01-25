<?php


namespace Rocket\Footer\JS\Integration;


class BNEFlyOut extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( ( function_exists( 'bne_flyout_setup' ) || class_exists( 'BNE_Flyouts' ) ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 100 );
		}
	}

	public function scripts() {
		wp_add_inline_script( 'sidr', '(function($){if(!window.MutationObserver){return;}var observer = new MutationObserver(function() {$(window).lazyLoadXT();}); observer.observe($(".flyout-overlay").get(0), {attributes: true});})(jQuery);' );
	}
}
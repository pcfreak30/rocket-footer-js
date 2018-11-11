<?php


namespace Rocket\Footer\JS\Integration;


class Elementor extends IntegrationAbstract {
	public function init() {
		if ( class_exists( '\Elementor\Plugin' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_action( 'elementor/frontend/after_register_scripts', [ $this, 'elementor_scripts' ] );
		}
	}

	public function elementor_scripts() {
		wp_add_inline_script( 'elementor-frontend', '(function(a){a(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/tabs.default",function(a,b){a.find(".elementor-tab-content.elementor-active").css("display","block")},11)})})(jQuery);' );
	}
}
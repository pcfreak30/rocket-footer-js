<?php


namespace Rocket\Footer\JS\Integration;


class ElementorUltimateAddons extends IntegrationAbstract {
	public function init() {
		if ( class_exists( '\UltimateElementor\UAEL_Core_Plugin' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			add_action( 'elementor/frontend/after_register_scripts', [ $this, 'elementor_scripts' ] );
		}
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', '(function($){$(function(){$(".uael-grid-gallery-img img").on("lazyload",function(){var $el=$(this).closest(".uael-img-carousel-wrap");$el.trigger("afterChange",[{$slider:$el}])});$(".uael-img-carousel-wrap").on("keydown.slick dragstart",function(){$(window).lazyLoadXT()})})})(jQuery);' );
	}

	public function elementor_scripts() {
		wp_add_inline_script( 'elementor-frontend', '(function($){$(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/uael-ba-slider.default",function($scope){$scope.imagesLoaded(function(){$scope.removeAttr("style");$scope.lazyLoadXT().on("lazyload",function(){$(window).trigger("resize.twentytwenty")})})},11)});window.addEventListener("PreloaderDestroyed",function(){$(".elementor-widget-uael-ba-slider").removeAttr("style");$(window).trigger("resize.twentytwenty")})})(jQuery);' );
	}
}

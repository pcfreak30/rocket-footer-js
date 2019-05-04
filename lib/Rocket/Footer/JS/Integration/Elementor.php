<?php


namespace Rocket\Footer\JS\Integration;


use Elementor\Core\Responsive\Responsive;
use Elementor\Element_Base;

class Elementor extends IntegrationAbstract {
	public function init() {
		if ( class_exists( '\Elementor\Plugin' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_action( 'elementor/frontend/after_register_scripts', [ $this, 'elementor_scripts' ] );
			add_action( 'elementor/frontend/column/before_render', [ $this, 'lazyload_background' ] );
			add_action( 'elementor/frontend/section/before_render', [ $this, 'lazyload_background' ] );
			add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
		}
	}

	public function elementor_scripts() {
		wp_add_inline_script( 'elementor-frontend', '(function(a){a(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/tabs.default",function(a,b){a.find(".elementor-tab-content.elementor-active").css("display","block")},11)})})(jQuery);' );
		wp_add_inline_script( 'elementor-frontend', '(function(a){a(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/image.default",function(a,b){a.find("img").on("lazyload", function(){b(this).removeClass("lazy-hidden")})})})})(jQuery);' );
	}

	public function lazyload_background( Element_Base $element ) {
		$settings             = array_filter( $element->get_settings_for_display() );
		$setting_keys_desktop = [
			'background_image'          => true,
			'background_overlay'        => true,
			'background_video_fallback' => true,
		];
		$setting_keys         = [];
		foreach ( $setting_keys_desktop as $setting_key ) {
			$setting_keys ["{$setting_key}_mobile"] = true;
			$setting_keys ["{$setting_key}_tablet"] = true;
		}

		$setting_keys = array_merge( $setting_keys, $setting_keys_desktop );
		$found        = array_intersect_key( $settings, $setting_keys );
		if ( $found ) {
			$element->add_render_attribute( '_wrapper', 'data-lazyload-bg', 1 );
		}
	}

	public function enqueue_styles() {
		$breakpoints = Responsive::get_breakpoints();
		$style       = <<<CSS
 .elementor-element[data-lazyload-bg], .elementor-element[data-lazyload-bg] > .elementor-background-overlay, .elementor-element[data-lazyload-bg] > .elementor-motion-effects-container > .elementor-motion-effects-layer, .elementor-element[data-lazyload-bg] > .elementor-element-populated {
    background: none !important;
}
CSS;
		$css         = $style;

		foreach ( $breakpoints as $breakpoint ) {
			$css .= "@media(max-width:{$breakpoint}px){$style}";
		}
		wp_add_inline_style( 'elementor-frontend', $css );
	}
}

<?php


namespace Rocket\Footer\JS\Integration;


use Elementor\Core\Responsive\Responsive;
use Elementor\Widget_Base;

class ElementorPro extends IntegrationAbstract {

	public function init() {
		add_action( 'wp', [ $this, 'wp_action' ] );
	}


	public function wp_action() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		if ( ! $this->plugin->lazyload_manager->is_enabled() ) {
			return;
		}
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'elementor_scripts' ] );
		add_action( 'elementor/widget/render_content', [ $this, 'lazyload_slider' ], 10, 2 );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
		add_filter( 'rocket_footer_js_elementor_lazyload_widgets', [ $this, 'lazyload_widgets' ] );
	}

	public
	function elementor_scripts() {
		wp_add_inline_script( 'elementor-frontend', '(function(a){a(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/posts.cards",function(a,b){b(window).trigger("resize")},11)})})(jQuery);' );
		wp_add_inline_script( 'elementor-frontend', '(function(a) {
    a(window).on("elementor/frontend/init", function() {
        elementorFrontend.hooks.addAction("frontend/element_ready/slides.default", function(a, b) {
            var slick = a.find(".elementor-slides");
            slick.slick("getSlick").$slides.eq(slick.slick("slickCurrentSlide")).find(".slick-slide-bg").removeClass("hide");
            b(window).lazyLoadXT();
            slick.on("beforeChange", function(event, slick, currentSlide, nextSlide) {
              slick.$slides.eq(nextSlide).find(".slick-slide-bg").removeClass("hide");
            })
        }, 11)
    })
})(jQuery);' );
	}

	public
	function lazyload_slider(
		$widget_content, Widget_Base $widget_base
	) {
		if ( 'slides' === $widget_base->get_name() ) {
			$widget_content = str_replace( 'class="slick-slide-bg', 'data-lazyload-bg="1" class="lazyload slick-slide-bg hide', $widget_content );
		}

		return $widget_content;
	}

	public
	function enqueue_styles() {
		$breakpoints = Responsive::get_breakpoints();
		$style       = <<<CSS
 .elementor-widget-slides .slick-slide > .slick-slide-bg[data-lazyload-bg]  {
    background: none !important;
}
 .elementor-widget-slides .slick-slide > .slick-slide-bg[data-lazyload-bg].hide  {
    visibility: hidden;
}
CSS;
		$css         = $style;

		foreach ( $breakpoints as $breakpoint ) {
			$css .= "@media(max-width:{$breakpoint}px){$style}";
		}
		wp_add_inline_style( 'elementor-frontend', $css );
	}

	public
	function lazyload_widgets(
		$widgets
	) {
		return array_merge( $widgets, [
			'theme-site-logo',
			'theme-post-featured-image',
			'woocommerce-category-image',
		] );
	}
}

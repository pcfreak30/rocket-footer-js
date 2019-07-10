<?php


namespace Rocket\Footer\JS\Integration;


class DiviPopupBuilder extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_action( 'init', [ $this, 'init_action' ], 10000 );
	}

	public function init_action() {
		if ( class_exists( '\et_pb_popup_builder' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 10000 );
		}
	}

	public function scripts() {
		if ( $this->plugin->lazyload_manager->is_enabled() ) {
			$script = wp_scripts()->registered['sb_dpb_colorbox_js'];
			wp_dequeue_script( 'sb_dpb_colorbox_js' );
			wp_deregister_script( 'sb_dpb_colorbox_js' );
			wp_enqueue_script( 'sb_dpb_colorbox_js', $script->src, [ 'jquery-lazyloadxt.videoembed' ] );
			wp_add_inline_script( 'sb_dpb_colorbox_js', '(function($){$(document).on("cbox_open",function(){var process=function(){if(window.et_fix_video_wmode)et_fix_video_wmode(container);if($.fn.fitVids)container.fitVids();$.colorbox.resize()};var container=$.colorbox.element().parent().find(".sb_divi_modal");var el=container.find("[data-lazy-video-embed], .lazyload, .lazyloadwait, .lazyloading");el.one("lazyload load",function(){el.parent().find("iframe").trigger("lazyshow");process()}).click();container.one("lazyload",process);$(window).lazyLoadXT()});$(document).on("cbox_complete",function(){$(window).trigger("resize")})})(jQuery);' );
		}
	}
}

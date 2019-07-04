<?php


namespace Rocket\Footer\JS\Integration;


class Ewww extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( class_exists( 'EWWW_Image' ) && ewww_image_optimizer_get_option( 'ewww_image_optimizer_cloud_key' ) ) {
			add_action( 'rocket_footer_js_lazyload_video_before_maybe_generate_thumbnails', [ $this, 'disable' ] );
		}
	}

	public function disable() {
		global $ewww_preempt_editor;
		$ewww_preempt_editor = true;
	}
}

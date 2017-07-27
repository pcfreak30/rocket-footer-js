<?php


namespace Rocket\Footer\JS\Integration;


class RevolutionSlider implements IntegrationInterface {

	public function init() {
		if ( function_exists( 'rev_slider_shortcode' ) ) {
			add_filter( 'option_revslider-global-settings', [ $this, 'modify_settings' ] );
		}
	}

	public function modify_settings( $options ) {
		$options['load_all_javascript'] = 'on';

		return $options;
	}

}
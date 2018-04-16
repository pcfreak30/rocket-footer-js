<?php


namespace Rocket\Footer\JS\Integration;


class RevolutionSlider extends IntegrationAbstract {

	public function init() {
		if ( function_exists( 'rev_slider_shortcode' ) ) {
			add_filter( 'option_revslider-global-settings', [ $this, 'modify_settings' ] );
		}
	}

	public function modify_settings( $options ) {
		if ( isset( $options['load_all_javascript'] ) ) {
			$options['load_all_javascript'] = 'on';
		}

		return $options;
	}

}
<?php


namespace Rocket\Footer\JS\Integration;


class Divi extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( function_exists( 'et_setup_theme' ) ) {
			add_filter( 'et_get_option_divi_minify_combine_scripts', [ $this, 'return_false' ] );
		}
	}

	public function return_false() {
		return 'false';
	}
}

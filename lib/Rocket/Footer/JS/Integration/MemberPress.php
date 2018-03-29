<?php


namespace Rocket\Footer\JS\Integration;


class MemberPress extends IntegrationAbstract {
	public function init() {
		if ( class_exists( 'MeprZxcvbnCtrl' ) && 0 < (int) get_rocket_option( 'cdn' ) ) {
			add_filter( 'mepr-signup-scripts', [ $this, 'filter_scripts' ], 11 );
		}
	}

	public function filter_scripts( $scripts ) {
		if ( wp_script_is( 'mepr-zxcvbn', 'registered' ) ) {
			$i18n               = \MeprZxcvbnCtrl::get_i18n_array();
			$i18n['script_url'] = get_rocket_cdn_url( $i18n['script_url'], [ 'all', 'js', 'css_and_js' ] );
			wp_scripts()->add_data( 'mepr-zxcvbn', 'data', '' );
			wp_localize_script( 'mepr-zxcvbn', 'MeprZXCVBN', $i18n );
		}

		return $scripts;
	}
}
<?php


namespace Rocket\Footer\JS\Integration;


class ThriveThemeFramework extends IntegrationAbstract {
	public function init() {
		if ( 0 < (int) get_rocket_option( 'cdn' ) && ! is_admin() ) {
			add_action( 'after_setup_theme', [ $this, 'theme_check' ], 11 );
		}
	}

	public function theme_check() {
		if ( class_exists( 'Thrive_Theme_Setup' ) ) {
			add_filter( 'option_thrive_theme_options', [ $this, 'modify_theme_options' ] );
		}
	}

	public function modify_theme_options( $options ) {
		if ( isset( $options['logo'] ) ) {
			$options['logo']      = get_rocket_cdn_url( $options['logo'], [ 'all', 'images' ] );
			$options['logo_dark'] = get_rocket_cdn_url( $options['logo_dark'], [ 'all', 'images' ] );
		}

		return $options;
	}
}
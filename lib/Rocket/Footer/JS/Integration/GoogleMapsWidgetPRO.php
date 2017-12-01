<?php


namespace Rocket\Footer\JS\Integration;


class GoogleMapsWidgetPRO extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\GMWP' ) ) {
			add_action( 'wp_footer', [ $this, 'scripts' ], 11 );
		}

	}

	public function scripts() {
		if ( wp_script_is( 'gmw' ) ) {
			$localized_data = wp_scripts()->get_data( 'gmw', 'data' );
			if ( preg_match( '~("colorbox_css":)"(.*)"~', $localized_data, $matches ) ) {
				$localized_data = preg_replace( '~("colorbox_css":)".*"~', '$1false', $localized_data );
				wp_scripts()->add_data( 'gmw', 'data', $localized_data );
				wp_enqueue_style( 'gmw-colorbox', wp_unslash( $matches[2] ), [], null );
			}
		}
	}
}
<?php


namespace Rocket\Footer\JS\Integration;


class GoogleMapsWidget extends IntegrationAbstract {

	private $options;

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\GMWP' ) ) {
			add_action( 'wp_footer', [ $this, 'scripts' ], 11 );
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				$this->options = \GMWP::get_options();
				add_filter( 'do_shortcode_tag', [ $this, 'process' ], 10, 2 );
			}
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

	public function process( $output, $tag ) {
		if ( $this->options['sc_map'] === $tag ) {
			$output = apply_filters( 'a3_lazy_load_images', $output );
		}

		return $output;
	}
}

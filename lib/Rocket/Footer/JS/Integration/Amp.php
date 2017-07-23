<?php


namespace Rocket\Footer\JS\Integration;


class Amp implements IntegrationInterface {

	public function init() {
		add_action( 'wp', [ $this, 'wp_action' ] );
	}

	public function wp_action() {
		if ( defined( 'AMP_QUERY_VAR' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			remove_filter( 'rocket_buffer', [ rocket_footer_js(), 'process_buffer' ], PHP_INT_MAX );
		}
	}
}
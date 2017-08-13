<?php


namespace Rocket\Footer\JS\Integration;


class Amp extends IntegrationAbstract {

	public function init() {
		add_action( 'wp', [ $this, 'wp_action' ] );
	}

	public function wp_action() {
		if ( defined( 'AMP_QUERY_VAR' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			remove_filter( 'rocket_buffer', [ $this->app, 'process_buffer' ], PHP_INT_MAX );
		}
	}
}
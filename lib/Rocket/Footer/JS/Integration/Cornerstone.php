<?php


namespace Rocket\Footer\JS\Integration;


class Cornerstone extends IntegrationAbstract {

	public function init() {
		if ( function_exists( 'cornerstone_plugin_init' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
		}
	}

	public function enqueue_scripts() {
		wp_add_inline_script( 'cornerstone-site-body', "jQuery(function(){document.dispatchEvent(new Event('DOMContentLoaded'));});" );
	}
}
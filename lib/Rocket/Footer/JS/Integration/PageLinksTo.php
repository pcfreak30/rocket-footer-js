<?php


namespace Rocket\Footer\JS\Integration;


class PageLinksTo extends IntegrationAbstract {

	public function init() {
		if ( class_exists( 'CWS_PageLinksTo' ) ) {
			add_action( 'init', [ $this, 'disable_buffer' ], 12 );
		}
	}

	public function disable_buffer() {
		remove_action( 'wp_enqueue_scripts', array( \CWS_PageLinksTo::$instance, 'start_buffer' ), - 9999 );
		remove_action( 'wp_head', array( \CWS_PageLinksTo::$instance, 'end_buffer' ), 9999 );
	}
}
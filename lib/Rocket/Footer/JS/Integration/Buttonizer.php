<?php


namespace Rocket\Footer\JS\Integration;


class Buttonizer extends IntegrationAbstract {
	public function init() {
		if ( class_exists( '\Buttonizer\Button' ) ) {
			add_action( 'wp_footer', [ $this, 'buffer_start' ], 9 );
			add_action( 'wp_footer', [ $this, 'buffer_end' ], 11 );
		}
	}

	public function buffer_start() {
		ob_start( [ $this, 'process_buffer' ] );
	}

	public function process_buffer( $buffer ) {
		return preg_replace( '~document\s*\.\s*addEventListener\s*\(["\']DOMContentLoaded["\']\s*,(\s*function\s*\(\s*\)\s*\{.*buttonizer.*)~Us', "window.addEventListener('load'," . '$1', $buffer );
	}

	public function buffer_end() {
		ob_end_flush();
	}
}

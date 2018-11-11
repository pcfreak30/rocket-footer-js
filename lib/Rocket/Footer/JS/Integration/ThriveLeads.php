<?php


namespace Rocket\Footer\JS\Integration;


class ThriveLeads extends IntegrationAbstract {
	public function init() {
		if ( function_exists( 'tve_leads_init' ) && 0 < (int) get_rocket_option( 'cdn' ) ) {
			add_filter( 'do_shortcode_tag', [ $this, 'process' ], 10, 2 );
		}
	}

	public function process( $content, $tag ) {
		if ( 'thrive_leads' === $tag ) {
			$content = rocket_cdn_images( $content );
		}

		return $content;
	}
}
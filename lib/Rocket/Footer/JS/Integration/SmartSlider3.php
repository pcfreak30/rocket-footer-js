<?php


namespace Rocket\Footer\JS\Integration;


class SmartSlider3 extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\SmartSlider3' ) ) {
			add_action( 'rocket_footer_js_do_rewrites', [ $this, 'rewrite' ] );
		}
	}

	public function rewrite() {
		$xpath = new \DOMXPath( $this->plugin->document );
		/** @var \Rocket\Footer\JS\DOMElement $tag */
		foreach ( $xpath->query( '//div[@data-desktop]' ) as $tag ) {
			$tag->setAttribute( 'data-desktop', get_rocket_cdn_url( $tag->getAttribute( 'data-desktop' ), [
				'all',
				'images',
			] ) );
		}
	}
}
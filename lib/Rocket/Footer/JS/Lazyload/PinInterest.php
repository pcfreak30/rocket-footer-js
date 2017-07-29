<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Async\CSS\DOMElement;

class PinInterest extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( 'assets.pinterest.com' === parse_url( $src, PHP_URL_HOST ) && false !== strpos( parse_url( $src, PHP_URL_PATH ), 'js/pinit.js' ) ) {

			/** @var DOMElement $tag */
			foreach ( $this->xpath->query( '//*[@data-pin-do]' ) as $tag ) {
				$tag->setAttribute( 'data-lazy-widget', 'pin-interest' );
				if ( 0 == $tag->childNodes->length ) {
					$img = $this->create_pixel_image();
					$tag->setAttribute( 'data-lazy-widget', 'pin-interest' );
					$tag->appendChild( $img );
				}
			}
			$this->lazyload_script( $this->get_script_content( $this->create_tag( null, 'https://assets.pinterest.com/js/pinit_main.js' ) ), 'pin-interest' );
			$this->tags->remove();
		}
	}
}
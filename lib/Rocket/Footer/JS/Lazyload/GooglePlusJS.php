<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class GooglePlusJS extends LazyloadAbstract {

	protected $regex = '~\(\s*function\s*\(\s*\)\s*{.*\(\s*.*po\s*\.\s*src\s*=\s*["\']https://apis\s*.google\s*.com/js/(?:platform|plusone).js["\'];.*}\s*\)\s*\(\s*\)\s*;~';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( preg_match( $this->regex, $content, $matches ) ) {
			$tag_content = $this->get_script_content();
			$this->lazyload_script( $tag_content, 'google-plus-platform' );
			/** @var DOMElement $tag */
			foreach (
				array(
					'//g:plusone',
					'//*[contains(concat(" ", normalize-space(@class), " "), " g-plusone ")]',
					'//*[contains(concat(" ", normalize-space(@class), " "), " g-plus ")]',
				) as $expression
			) {
				foreach ( $this->xpath->query( $expression ) as $tag ) {
					$tag->setAttribute( 'data-lazy-widget', 'google-plus-platform' );
					if ( 0 == $tag->childNodes->length ) {
						$img = $this->create_pixel_image();
						$tag->setAttribute( 'data-lazy-widget', "google-plus-platform" );
						$tag->appendChild( $img );
					}
				}
			}

		}
	}

}
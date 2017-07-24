<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMElement;

class GooglePlus extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( false !== strpos( $src, 'apis.google.com/js/platform.js' ) ) {
			$this->lazyload_script( "<script type=\"text/javascript\">    (function() {      var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;      po.src = 'https://apis.google.com/js/platform.js';      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);    })();  </script>", 'google-plus-platform' );
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

	/**
	 * @param string $content
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_lazyload_off( $content, $src ) {
		if ( false !== strpos( $src, 'apis.google.com/js/platform.js' ) ) {
			$this->tags->current()->setAttribute( 'data-no-minify', '1' );
		}
	}
}
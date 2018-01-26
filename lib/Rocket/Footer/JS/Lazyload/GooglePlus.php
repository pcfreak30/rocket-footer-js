<?php


namespace Rocket\Footer\JS\Lazyload;


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
		$this->lazyload_script( "<script type=\"text/javascript\">    (function() {      var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;      po.src = 'https://apis.google.com/js/platform.js';      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);    })();  </script>", 'google-plus-platform' );
		$this->xpath->registerNamespace( 'g', null );
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
					$tag->setAttribute( 'data-lazy-widget', 'google-plus-platform' );
					$tag->appendChild( $img );
				}
			}
		}

	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && false !== strpos( $src, 'apis.google.com/js/platform.js' );
	}
}
<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class Recaptcha extends LazyloadAbstract {

	private $loaded = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag_content = $this->get_script_content();
		if ( ! $this->loaded ) {
			$this->lazyload_script( $tag_content, 'recaptcha' );
			$this->loaded = true;
		} else {
			$this->tags->remove();
		}

		/** @var DOMElement $tag */
		foreach ( $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " g-recaptcha ")]' ) as $tag ) {
			$tag->setAttribute( 'data-lazy-widget', 'recaptcha' );
		}
	}

	protected function is_match( $content, $src ) {
		return ( ( 'google.com' === parse_url( $src, PHP_URL_HOST ) || 'www.google.com' === parse_url( $src, PHP_URL_HOST ) ) && '/recaptcha/api.js' === parse_url( $src, PHP_URL_PATH ) );
	}
}

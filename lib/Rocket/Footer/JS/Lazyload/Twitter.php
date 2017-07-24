<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMElement;

class Twitter extends LazyloadAbstract {

	protected $regex = '~(?:window\.twttr\s*=\s*\(|!|\()\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*\(\s*document\s*,\s*[\'"]script[\'"]\s*,\s*[\'"]twitter-wjs[\'"]\s*(?:\)\);|\);)~';

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
			$this->lazyload_script( $tag_content, 'twitter-sdk' );
			/** @var DOMElement $tag */
			foreach (
				array(
					'twitter-share-button',
					'twitter-hashtag-button',
					'twitter-mention-button',
					'twitter-dm-button',
					'twitter-follow-button',
				) as $class
			) {
				foreach ( $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]' ) as $tag ) {
					$tag->setAttribute( 'data-lazy-widget', 'twitter-sdk' );
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
		if ( preg_match( $this->regex, $content, $matches ) ) {
			$this->set_no_minify();
		}
	}
}
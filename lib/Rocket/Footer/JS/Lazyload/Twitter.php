<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class Twitter extends LazyloadAbstract {

	protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
		$regex = '~(?:window\.twttr\s*=\s*\(|!|\()\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*\(\s*document\s*,\s*[\'"]script[\'"]\s*,\s*[\'"]twitter-wjs[\'"]\s*(?:\)\);|\);)~';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
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
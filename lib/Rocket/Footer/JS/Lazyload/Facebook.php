<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class Facebook extends LazyloadAbstract {

	protected $sdk_loaded = false;
	protected $regex = '~\(\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*js\.src\s*=\s*"//connect\.facebook.net/[\w_]+/(?:sdk|all)\.js(?:#(?:&?xfbml=\d|(?:&?version=[\w\.]+)|(?:&?appId=\d*)&?)+)?"\s*;.*\s*\'facebook-jssdk\'\s*\)\);?~is';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		/** @noinspection NotOptimalRegularExpressionsInspection */
		if ( preg_match( $this->regex, $content, $matches ) ) {
			if ( ! $this->sdk_loaded ) {
				$tag_content = $this->get_script_content();
				$this->lazyload_script( $tag_content, 'facebook-sdk' );
				/** @var DOMElement $tag */
				foreach (
					array(
						'fb-page',
						'fb-like',
						'fb-like-box',
						'fb-quote',
						'fb-send',
						'fb-share-button',
						'fb-follow',
						'fb-video',
						'fb-post',
						'fb-comment-embed',
						'fb-comments',
					) as $class
				) {
					foreach ( $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]' ) as $tag ) {
						$tag->setAttribute( 'data-lazy-widget', 'facebook-sdk' );
					}
				}
				$this->sdk_loaded = true;
			} else {
				$this->tags->remove();
			}

		}
	}
}
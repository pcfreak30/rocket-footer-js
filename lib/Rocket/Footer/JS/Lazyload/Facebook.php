<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMElement;

class Facebook extends LazyloadAbstract {

	protected $sdk_loaded = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		/** @noinspection NotOptimalRegularExpressionsInspection */
		if ( preg_match( '~\(\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*js\.src\s*=\s*"//connect\.facebook.net/[\w_]+/(?:sdk|all)\.js(?:#(?:&?xfbml=\d|(?:&?version=[\w\.\d]+)|(?:&?appId=\d*)&?)+)?"\s*;.*\s*\'facebook-jssdk\'\s*\)\);?~is', $content, $matches ) ) {
			if ( ! $this->sdk_loaded ) {
				$tag_content = str_replace(
					[ "\n", "\r", '<script>//', '//</script>' ],
					[
						'',
						'',
						'<script>',
						'</script>',
					], $this->document->saveHTML( $this->tags->current() ) );
				$this->lazyload_script( $tag_content, 'facebook-sdk' );
				/** @var DOMElement $tag */
				foreach (
					array(
						'fb-page',
						'fb-like',
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

	/**
	 * @param string $content
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_lazyload_off( $content, $src ) {
		$this->tags->current()->setAttribute( 'data-no-minify', '1' );
	}
}
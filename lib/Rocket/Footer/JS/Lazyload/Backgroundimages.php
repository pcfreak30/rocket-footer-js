<?php


namespace Rocket\Footer\JS\Lazyload;


class Backgroundimages extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {

	}

	protected function after_do_lazyload() {
		$xpath = new \DOMXPath( $this->document );
		foreach ( $xpath->query( '//*[@style]' ) as $tag ) {
			preg_match_all( '/url\\(\\s*([\'"](.*?)[\'"]|[^\\)\\s]+)\\s*\\)/', $tag->getAttribute( 'style' ), $matches );
			if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
				$match = array_shift( $matches[2] );
				if ( empty( $match ) ) {
					$match = array_shift( $matches[1] );
				}
				if ( 0 === strpos( $match, 'data:' ) ) {
					continue;
				}
				$match = trim( $match, '"' . "'" );
				$match = get_rocket_cdn_url( $match );
				$style = str_replace( $matches[0][0], 'none', $tag->getAttribute( 'style' ) );
				$tag->setAttribute( 'style', $style );
				$tag->setAttribute( 'data-bg', $match );
			}
		}

	}

	protected function is_match( $content, $src ) {
		return false;
	}
}
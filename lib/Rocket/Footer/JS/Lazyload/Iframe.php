<?php


namespace Rocket\Footer\JS\Lazyload;


class Iframe extends LazyloadAbstract {

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
		if ( ! $this->is_enabled() ) {
			return;
		}
		foreach ( $this->get_tag_collection( 'iframe' ) as $tag ) {
			if ( $this->is_no_lazyload( $tag ) || ( null !== $tag->parentNode && 'noscript' === strtolower( $tag->parentNode->tagName ) ) ) {
				continue;
			}
			$data_src = $tag->getAttribute( 'data-src' );
			if ( empty( $data_src ) ) {
				$src = $tag->getAttribute( 'src' );
				if ( ! empty( $src ) ) {
					$tag->setAttribute( 'data-src', $src );
					$tag->removeAttribute( 'src' );
				}
			}
		}
	}

	protected function is_match( $content, $src ) {
		return false;
	}
}
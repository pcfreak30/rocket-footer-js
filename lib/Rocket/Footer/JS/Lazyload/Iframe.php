<?php


namespace Rocket\Footer\JS\LazyLoad;


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
		foreach ( $this->get_tag_collection( 'iframe' ) as $tag ) {
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
}
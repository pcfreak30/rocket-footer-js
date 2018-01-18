<?php


namespace Rocket\Footer\JS\Lazyload;


class RevolutionSlider extends LazyloadAbstract {

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
		foreach ( $this->xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " rev_slider_wrapper ")]//div[contains(concat(" ", normalize-space(@class), " "), " tp-caption ")]//img[@data-lazyload]|//div[contains(concat(" ", normalize-space(@class), " "), " rev_slider_wrapper ")]//div[contains(concat(" ", normalize-space(@class), " "), " tp-caption ")]//img[@data-no-retina]' ) as $tag ) {
			if ( $this->is_no_lazyload( $tag ) ) {
				continue;
			}
			$data_lazyload           = $tag->getAttribute( 'data-lazyload' );
			$data_lazyload_no_retina = $tag->getAttribute( 'data-no-retina' );
			if ( empty( $data_lazyload ) && $data_lazyload_no_retina ) {
				continue;
			}
			$src = $data_lazyload_no_retina;
			if ( empty( $src ) ) {
				$src = $data_lazyload;
			}
			$tag->setAttribute( 'data-src', get_rocket_cdn_url( $src ) );
			$tag->setAttribute( 'src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' );
			$tag->removeAttribute( 'data-lazyload' );
			$tag->removeAttribute( 'data-no-retina' );
		}
	}
}
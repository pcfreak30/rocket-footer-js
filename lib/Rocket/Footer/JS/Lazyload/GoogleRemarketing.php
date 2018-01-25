<?php


namespace Rocket\Footer\JS\Lazyload;


class GoogleRemarketing extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {

	}

	protected function do_lazyload_off( $content, $src ) {
		$tag = $this->tags->current();
		$this->set_no_minify();
		$js_node = $tag->prev( 'script[text()[contains(.,"google_conversion_id")]]' );
		if ( ! empty( $js_node ) ) {
			$this->set_no_minify( $js_node );
		}
		$this->tags->flag_removed();

	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && 'googleadservices.com' === parse_url( $src, PHP_URL_HOST );
	}
}
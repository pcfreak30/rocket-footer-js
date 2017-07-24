<?php


namespace Rocket\Footer\JS\LazyLoad;


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
		if ( 'googleadservices.com' === parse_url( $src, PHP_URL_HOST ) && ! $this->is_no_minify() ) {
			$tag = $this->tags->current();
			$this->set_no_minify();
			$prev_tag = $tag;
			do {
				$prev_tag = $prev_tag->previousSibling;
			} while ( null !== $prev_tag && ! ( XML_ELEMENT_NODE == $prev_tag->nodeType && 'script' === strtolower( $prev_tag->tagName ) && false !== strpos( $prev_tag->textContent, 'google_conversion_id' ) ) );
			$js_node = $prev_tag;
			if ( null !== $prev_tag ) {
				$this->set_no_minify( $js_node );
			}
			$this->tags->flag_removed();
		}
	}
}
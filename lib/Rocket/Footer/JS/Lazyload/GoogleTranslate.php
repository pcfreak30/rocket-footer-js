<?php


namespace Rocket\Footer\JS\Lazyload;


class GoogleTranslate extends LazyloadAbstract {
	/** @noinspection ClassOverridesFieldOfSuperClassInspection */
	protected $js_regex = '~google\.translate\.TranslateElement\s*\({.*}\s*,\s*[\'"](.*)[\'"]\s*\)\s*;~';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag      = $this->tags->current();
		$prev_tag = $tag;
		do {
			$prev_tag = $prev_tag->previousSibling;
		} while ( ! empty( $prev_tag ) && ! ( XML_ELEMENT_NODE == $prev_tag->nodeType && 'script' === strtolower( $prev_tag->tagName ) && preg_match( $this->js_regex, $prev_tag->textContent, $matches ) ) );
		$js_node = $prev_tag;
		if ( ! empty( $js_node ) ) {
			/** @var array $matches */
			$translate_tag = $this->content_document->getElementById( $matches[1] );
			if ( ! empty( $translate_tag ) ) {
				$this->lazyload_script( $this->get_script_content( $js_node ) . $this->get_script_content(), 'google-translate' );
				$translate_tag->setAttribute( 'data-lazy-widget', 'google-translate' );
				$translate_tag->setAttribute( 'style', 'min-width:1px; min-height:1px;background:inherit;' );
				$js_node->parentNode->removeChild( $js_node );
				$this->tags->flag_removed();
			}
		}

	}

	protected function do_lazyload_off( $content, $src ) {
		$tag      = $this->tags->current();
		$prev_tag = $tag;
		do {
			$prev_tag = $prev_tag->previousSibling;
		} while ( ! empty( $prev_tag ) && ! ( XML_ELEMENT_NODE == $prev_tag->nodeType && 'script' === strtolower( $prev_tag->tagName ) && preg_match( $this->regex, $prev_tag->textContent, $matches ) ) );
		$js_node = $prev_tag;
		$this->set_no_minify( $js_node );
		$this->set_no_minify();

	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && 'translate.google.com' === parse_url( $src, PHP_URL_HOST );
	}
}
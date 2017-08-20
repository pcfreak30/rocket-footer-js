<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class GoogleAdsense extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag         = $this->tags->current();
		$sub_content = $this->get_script_content();
		/** @var DOMElement $next_tag */
		$next_tag = $tag;
		do {
			$next_tag = $next_tag->nextSibling;
		} while ( null !== $next_tag && ! ( XML_ELEMENT_NODE == $next_tag->nodeType && 'ins' === strtolower( $next_tag->tagName ) && false !== strpos( $next_tag->getAttribute( 'class' ), 'adsbygoogle' ) ) );
		$ad_node = $next_tag;
		if ( null !== $next_tag ) {
			$tag->setAttribute( 'data-no-minify', '1' );
			$next_tag = $tag;
		}
		do {
			$next_tag = $next_tag->nextSibling;
		} while ( ! ( XML_ELEMENT_NODE === $next_tag->nodeType && 'script' === strtolower( $next_tag->tagName ) && null !== $next_tag->textContent && false !== strpos( $next_tag->textContent, 'adsbygoogle' ) ) );
		$js_node = $next_tag;
		if ( null !== $next_tag ) {
			$sub_content .= $this->get_script_content( $js_node );
			$this->lazyload_script( $sub_content, "google-adsense-{$this->instance}" );
			$js_node->parentNode->removeChild( $js_node );
			$ad_node->setAttribute( 'data-lazy-widget', "google-adsense-{$this->instance}" );
			$this->instance ++;
			$this->tags->flag_removed();
		} else {
			$this->set_no_minify( $js_node );
		}
	}

	protected function do_lazyload_off( $content, $src ) {
		$this->set_no_minify();
		$next_tag = $this->tags->current();
		do {
			$next_tag = $next_tag->nextSibling;
		} while ( ! ( XML_ELEMENT_NODE === $next_tag->nodeType && 'script' === strtolower( $next_tag->tagName ) && null !== $next_tag->textContent && false !== strpos( $next_tag->textContent, 'adsbygoogle' ) ) );
		$js_node = $next_tag;
		if ( ! empty( $js_node ) ) {
			$this->set_no_minify( $js_node );
		}
	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && '' === trim( $this->tags->current()->getAttribute( 'data-lazyload-processed' ) ) && 'pagead2.googlesyndication.com' === parse_url( $src, PHP_URL_HOST );
	}
}
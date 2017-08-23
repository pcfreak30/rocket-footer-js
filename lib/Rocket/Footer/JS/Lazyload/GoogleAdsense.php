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

		$ad_node = $tag->next( 'ins[contains(concat(" ", normalize-space(@class), " "), " adsbygoogle ")]' );

		if ( ! empty( $ad_node ) ) {
			$sub_content = $this->get_script_content( $ad_node );
			$this->lazyload_script( $sub_content, "google-adsense-{$this->instance}" );
			$ad_node->parentNode->removeChild( $ad_node );
			$ad_node->setAttribute( 'data-lazy-widget', "google-adsense-{$this->instance}" );
			$this->instance ++;
		}
	}

	protected function do_lazyload_off( $content, $src ) {
		$this->set_no_minify();
		$tag     = $this->tags->current();
		$js_node = $tag->next( 'script[text()[contains(.,"adsbygoogle")]]' );
		if ( ! empty( $js_node ) ) {
			$this->set_no_minify( $js_node );
		}
	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && 'pagead2.googlesyndication.com' === parse_url( $src, PHP_URL_HOST );
	}
}
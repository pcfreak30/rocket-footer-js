<?php


namespace Rocket\Footer\JS\Lazyload;


class VK extends LazyloadAbstract {

	private $js_tag;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		/** @noinspection Annotator */
		$share_script = <<<JS
(function check () {
  if (typeof VK === 'undefined') {
    setTimeout(check, 10);
  }
  else {
    document.querySelector('[data-lazy-widget="vk-share-{$this->instance}"]').innerHTML = {$this->regex_match[1]};
  }
})();
JS;

		$share_script = $this->create_script( $share_script );
		$span         = $this->create_tag( 'span' );
		$img          = $this->create_pixel_image();
		$span->setAttribute( 'data-lazy-widget', "vk-share-{$this->instance}" );
		$span->appendChild( $img );
		if ( null !== $this->js_tag->nextSibling ) {
			$this->js_tag->parentNode->appendChild( $span );
		} else {
			$this->js_tag->parentNode->insertBefore( $this->js_tag->nextSibling, $span );
		}
		$tag_content = $this->get_tag_content();
		$this->lazyload_script( $tag_content . $this->get_script_content( $share_script ), "vk-share-{$this->instance}" );
		$this->js_tag->parentNode->removeChild( $this->js_tag );
		$this->tags->flag_removed();
		$this->instance ++;

	}

	protected function do_lazyload_off( $content, $src ) {
		$this->set_no_minify();
		$this->set_no_minify( $this->js_tag );
	}

	protected function is_match( $content, $src ) {
		$match    = 'vk.com' === parse_url( $src, PHP_URL_HOST );
		$tag      = $this->tags->current();
		$next_tag = $tag;
		do {
			$next_tag = $next_tag->nextSibling;
		} while ( null !== $next_tag && XML_ELEMENT_NODE !== $next_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );
		$match = $match && parent::is_match( $next_tag->textContent, $src );
		if ( $match ) {
			$this->js_tag = $next_tag;
		}

		return $match;
	}
}
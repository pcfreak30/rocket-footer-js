<?php


namespace Rocket\Footer\JS\Lazyload;


class VK extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag      = $this->tags->current();
		$next_tag = $tag;
		do {
			$next_tag = $next_tag->nextSibling;
		} while ( null !== $next_tag && XML_ELEMENT_NODE !== $next_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );

		if ( preg_match( '~document\s*.\s*write\s*\(\s*(VK\s*.\s*Share\s*.\s*button.*.\s*\))\s*\);~', $next_tag->textContent, $matches ) ) {
			/** @noinspection Annotator */
			$share_script = <<<JS
(function check () {
  if (typeof VK === 'undefined') {
    setTimeout(check, 10);
  }
  else {
    document.querySelector('[data-lazy-widget="vk-share-{$this->instance}"]').innerHTML = {$matches[1]};
  }
})();
JS;

			$share_script = $this->create_script( $share_script );
			$span         = $this->create_tag( 'span' );
			$img          = $this->create_pixel_image();
			$span->setAttribute( 'data-lazy-widget', "vk-share-{$this->instance}" );
			$span->appendChild( $img );
			if ( null !== $next_tag->nextSibling ) {
				$next_tag->parentNode->appendChild( $span );
			} else {
				$next_tag->parentNode->insertBefore( $next_tag->nextSibling, $span );
			}
			$tag_content = $this->get_tag_content();
			$this->lazyload_script( $tag_content . $this->get_script_content( $share_script ), "vk-share-{$this->instance}" );
			$next_tag->parentNode->removeChild( $next_tag );
			$this->instance ++;
		}
	}

	protected function do_lazyload_off( $content, $src ) {
		if ( 'vk.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$tag      = $this->tags->current();
			$next_tag = $tag;
			do {
				$next_tag = $next_tag->nextSibling;
			} while ( null !== $next_tag && XML_ELEMENT_NODE !== $next_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );
			$this->set_no_minify();
			$this->set_no_minify( $next_tag );
		}
	}
}
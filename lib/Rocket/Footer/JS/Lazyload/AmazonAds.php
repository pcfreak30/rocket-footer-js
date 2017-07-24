<?php


namespace Rocket\Footer\JS\LazyLoad;


class AmazonAds extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( false !== strpos( $src, 'amazon-adsystem.com' ) ) {
			$tag      = $this->tags->current();
			$prev_tag = $tag;
			do {
				$prev_tag = $prev_tag->nextSibling;
			} while ( null !== $prev_tag && XML_ELEMENT_NODE !== $prev_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );
			$sub_content = $this->get_script_content() . $this->get_tag_content( $prev_tag );
			$img         = $this->create_pixel_image();
			$span        = $this->create_tag( 'span' );
			$span->setAttribute( 'data-lazy-widget', "amazon-ads-{$this->instance}" );
			$span->appendChild( $img );
			$prev_tag->parentNode->removeChild( $prev_tag );
			$tag->parentNode->insertBefore( $span, $tag );
			$this->lazyload_script( $sub_content, "amazon-ads-{$this->instance}" );
			$tag->setAttribute( 'data-lazy-widget', "amazon-ads-{$this->instance}" );
			$this->instance ++;
			$this->tags->flag_removed();
		}
	}

	protected function do_lazyload_off( $content, $src ) {

		if ( false !== strpos( $src, 'amazon-adsystem.com' ) ) {
			$this->set_no_minify();
			$tag      = $this->tags->current();
			$prev_tag = $tag;
			do {
				$prev_tag = $prev_tag->previousSibling;
			} while ( null !== $prev_tag && XML_ELEMENT_NODE !== $prev_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );
			$js_node = $prev_tag;
			if ( ! empty( $js_node ) ) {
				$this->set_no_minify( $prev_tag );
			}
		}

	}
}
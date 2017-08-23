<?php


namespace Rocket\Footer\JS\Lazyload;


class AmazonAds extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag     = $this->tags->current();
		$js_node = $tag->prev( "script" );
		if ( ! empty( $js_node ) ) {
			$sub_content = $this->get_script_content() . $this->get_tag_content( $js_node );
			$img         = $this->create_pixel_image();
			$span        = $this->create_tag( 'span' );
			$span->setAttribute( 'data-lazy-widget', "amazon-ads-{$this->instance}" );
			$span->appendChild( $img );
			$js_node->parentNode->removeChild( $js_node );
			$tag->parentNode->insertBefore( $span, $tag );
			$this->lazyload_script( $sub_content, "amazon-ads-{$this->instance}" );
			$tag->setAttribute( 'data-lazy-widget', "amazon-ads-{$this->instance}" );
			$this->instance ++;
			$this->tags->flag_removed();
		}

	}

	protected function do_lazyload_off( $content, $src ) {

		$this->set_no_minify();
		$tag     = $this->tags->current();
		$js_node = $tag->prev( "script" );
		if ( ! empty( $js_node ) ) {
			$this->set_no_minify( $js_node );
		}

	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && false !== strpos( $src, 'amazon-adsystem.com' );
	}
}
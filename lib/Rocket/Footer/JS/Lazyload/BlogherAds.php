<?php


namespace Rocket\Footer\JS\LazyLoad;


class BlogherAds extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( 'ads.blogherads.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$tag             = $this->tags->current();
			$external_script = $this->create_script( 'document.old_write=document.old_write||document.write;document.write=function(data){if(document.currentScript)(function check(){if(typeof jQuery==="undefined")setTimeout(10,check);else jQuery(document.currentScript).before(data)})()};' );
			$span            = $this->create_tag( 'span' );
			$img             = $this->create_pixel_image();
			$span->setAttribute( 'data-lazy-widget', "blogherads-{$this->instance}" );
			$span->appendChild( $img );
			$tag->parentNode->appendChild( $span );
			$this->lazyload_script( $this->get_script_content( $external_script ) . $this->get_script_content(), "blogherads-{$this->instance}" );
			$this->instance ++;
		}
	}
}
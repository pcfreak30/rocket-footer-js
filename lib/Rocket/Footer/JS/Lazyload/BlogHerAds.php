<?php


namespace Rocket\Footer\JS\Lazyload;


class BlogHerAds extends LazyloadAbstract {

	private $base64_injected = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( 'ads.blogherads.com' === parse_url( $src, PHP_URL_HOST ) || preg_match( '~blogherads\s*.\s*adq\s*.\s*push\s*\(\s*\[[\'"].*[\'"]\s*,\s*[\'"](.*)[\'"]\s*\]\s*\)\s*;~U', $content, $matches ) || preg_match( '~blogherads\s*.\s*defineSlot\s*\(\s*[\'"].*[\'"]\s*,\s*[\'"](.*)[\'"]\s*\s*\)~U', $content, $matches ) ) {
			$tag              = $this->tags->current();
			$lazyload_content = '';
			if ( ! empty( $matches ) ) {
				$prev_tag = $tag;
				do {
					$prev_tag = $prev_tag->previousSibling;
				} while ( null !== $prev_tag && XML_ELEMENT_NODE !== $prev_tag->nodeType && 'div' !== strtolower( $tag->tagName ) && $matches[1] !== $tag->getAttribute( 'id' ) );
				$div_tag = $prev_tag;
				if ( ! empty( $div_tag ) ) {
					$lazyload_content = $this->get_script_content( $div_tag ) . $this->get_script_content();
				}
			}
			$this->inject_tag( $this->create_script( 'document.old_write=document.old_write||document.write;document.write=function(data){if(document.currentScript)(function check(){if(typeof jQuery==="undefined")setTimeout(10,check);else jQuery(document.currentScript).before(data)})()};' ) );
			$file = rocket_footer_js()->remote_fetch( $src );
			if ( ! empty( $file ) && false !== strpos( $file, 'static/blogherads.js' ) ) {
				$prev_tag = $tag;
				do {
					$prev_tag = $prev_tag->previousSibling;
				} while ( null !== $prev_tag && XML_ELEMENT_NODE !== $prev_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );
				$js_tag = $prev_tag;
				if ( ! $this->base64_injected ) {
					$this->inject_tag( $this->create_script( null, 'https://cdnjs.cloudflare.com/ajax/libs/Base64/1.0.1/base64.min.js' ) );
					$this->base64_injected = true;;
				}
				$comment_tag  = $this->content_document->createComment( $this->get_script_content( $js_tag ) . $this->get_script_content( $tag ) );
				$external_tag = $this->content_document->createElement( 'div' );
				$external_tag->appendChild( $comment_tag );
				$external_tag->setAttribute( 'id', "blogherads-{$this->instance}" );
				$span = $this->create_tag( 'span' );
				$img  = $this->create_pixel_image();
				$span->setAttribute( 'data-lazy-widget', "blogherads-{$this->instance}" );
				$span->appendChild( $img );
				$this->append_tag( $span );
				$tag->parentNode->appendChild( $span );
				$window_check_script = $this->create_script(
					<<<JS
(function () {
	(window.addEventListener || window.attachEvent)((window.addEventListener ? '' : 'on') + 'load', function () {
		window.loaded = true;
	});
})();
JS
				);
				$this->set_no_minify( $window_check_script );
				$this->inject_tag( $window_check_script );
				$html = base64_encode( $this->get_script_content( $external_tag ) );
				$this->inject_tag( $this->create_script(
					<<<JS
(function ($) {
	var run = function () {
		var height = $(window).height();
		var html = "{$html}";
		var items = [];

		(function loop (node) {
			if (node.getBoundingClientRect) {
				var pos = parseInt(node.getBoundingClientRect().top + window.scrollY);
				if (pos - 100 > height)
				{
					if (!items[ pos ]) items[ pos ] = node;
				}
			}
			$(node).children().each(function (index, element) {
				loop(element)
			});
		})(document);
		var final_item = items.filter(Boolean).shift();
		if (final_item) $(final_item).before(atob(html))
	}
	if (window.loaded) {
		run();
		return;
	}
	$(window).load(run);
})(jQuery);
JS
				) );


				$this->tags->remove();
				$this->instance ++;

				return;
			}
			if ( empty( $lazyload_content ) ) {
				$lazyload_content = $this->get_script_content();
			}
			$span = $this->create_tag( 'span' );
			$img  = $this->create_pixel_image();
			$span->appendChild( $img );
			$span->setAttribute( 'data-lazy-widget', "blogherads-{$this->instance}" );
			$this->append_tag( $span );
			$this->lazyload_script( $lazyload_content, "blogherads-{$this->instance}" );
			$this->instance ++;
		}
	}
}
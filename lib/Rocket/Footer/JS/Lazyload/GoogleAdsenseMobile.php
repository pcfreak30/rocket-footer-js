<?php


namespace Rocket\Footer\JS\Lazyload;


class GoogleAdsenseMobile extends LazyloadAbstract {

	protected $regex = '~adsbygoogle.*google_ad_client.*enable_page_level_ads:\s*true~';
	private $base64_injected = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag = $this->tags->current();
		$tag->setAttribute( 'style', 'display:none' );
		if ( ! $this->base64_injected ) {
			$this->inject_tag( $this->create_script( null, 'https://cdnjs.cloudflare.com/ajax/libs/Base64/1.0.1/base64.min.js' ) );
			$this->base64_injected = true;;
		}
		$comment_tag  = $this->content_document->createComment( $this->get_script_content() );
		$external_tag = $this->content_document->createElement( 'div' );
		$external_tag->appendChild( $comment_tag );
		$external_tag->setAttribute( 'id', "google-adsense-mobile-{$this->instance}" );
		$span = $this->create_tag( 'span' );
		$img  = $this->create_pixel_image();
		$span->setAttribute( 'data-lazy-widget', "google-adsense-mobile-{$this->instance}" );
		$span->setAttribute( 'data-lazy-remove', "1" );
		$span->appendChild( $img );
		$this->append_tag( $span );
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
		$html = base64_encode( $this->get_script_content( $span ) . $this->get_script_content( $external_tag ) );
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
		})(document.body);
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
	}
}
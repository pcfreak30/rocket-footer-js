<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Async\CSS\DOMElement;

class PinInterest extends LazyloadAbstract {
	private $injected = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {

		/** @var DOMElement $tag */
		foreach ( $this->xpath->query( '//*[@data-pin-do]' ) as $tag ) {
			$img = $this->create_pixel_image();
			$img->setAttribute( 'data-lazy-widget', "pin-interest-{$this->instance}" );
			$tag->parentNode->insertBefore( $img, $tag );
			$tag->setAttribute( 'id', "pin-interest-{$this->instance}" );
			$external_script = $this->create_script(
				<<<JS
(function ($, w) {
	var el = $("#pin-interest-{$this->instance}");
	el.prev('[data-lazy-widget="pin-interest-{$this->instance}"]').remove();
	for (key in w) {
		if (key.match(/PIN_\d+/)) {
			w[ key ].f.util.build(el.parent().get(0));
		}
	}
})(jQuery, window);
JS
			);
			$this->lazyload_script( $this->get_script_content( $tag ) . $this->get_script_content( $external_script ), "pin-interest-{$this->instance}", $tag );
			$this->instance ++;
		}
		if ( ! $this->injected ) {
			$this->inject_tag( $this->create_script( null, 'https://assets.pinterest.com/js/pinit_main.js' ) );
			$this->injected = true;
		}
		$this->tags->remove();

	}

	protected function is_match( $content, $src ) {
		return parent::is_match( $content, $src ) && 'assets.pinterest.com' === parse_url( $src, PHP_URL_HOST ) && false !== strpos( parse_url( $src, PHP_URL_PATH ), 'js/pinit.js' );
	}
}
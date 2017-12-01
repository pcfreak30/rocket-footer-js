<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class QcodeGoogleMaps extends LazyloadAbstract {

	/**
	 * @var DOMElement
	 */
	protected $map_tag;

	public function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_check' ] );

	}

	public function theme_check() {
		if ( class_exists( '\QodeFramework' ) ) {
			parent::init();
		}
	}

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag = $this->tags->current();
		$div = $tag->next( '*[contains(concat(" ", normalize-space(@class), " "), " qode_google_map ")]' );
		if ( ! empty( $div ) ) {

			$new_script     = $this->create_script( '(function($){(function check(){if(typeof $==="undefined")setTimeout(check,10);else $.when($.getScript(' . wp_json_encode( $this->map_tag->getAttribute( 'src' ) ) . ' )' . ',$.Deferred(function(deferred){$(deferred.resolve)})).done(function(){if(document.readyState=="complete")$(showGoogleMap)})})()})(jQuery);' );
			$script_content = $this->get_script_content( $new_script ) . $this->get_script_content( $div );
			$this->map_tag->remove();

			$this->lazyload_script( $script_content, "qcode-google-maps-{$this->instance}", $div );
			$span = $this->create_tag( 'span' );
			$img  = $this->create_pixel_image();
			$span->setAttribute( 'data-lazy-widget', "qcode-google-maps-{$this->instance}" );
			$span->appendChild( $img );
			$this->append_tag( $span );
			$this->inject_tag( $span );
			$this->instance ++;
		}

		$this->tags->remove();
	}

	protected function before_do_lazyload() {
		$collection = $this->get_script_collection();
		/** @var DOMElement $tag */
		$map_instances = [];
		foreach ( $collection as $tag ) {
			$src = $tag->getAttribute( 'src' );
			if ( ! empty( $src ) ) {
				$src = rocket_add_url_protocol( $src );
			}
			if ( 'maps.googleapis.com' === parse_url( $src, PHP_URL_HOST ) ) {
				if ( preg_match( '/key=\w+/i', $src ) && empty( $this->map_tag ) ) {
					$this->map_tag = $tag;
				} else {
					$map_instances[] = $tag;
				}
			}
		}
		if ( empty( $this->map_tag ) && ! empty( $map_instances ) ) {
			$this->map_tag = array_shift( $map_instances );
		}
		foreach ( $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " qode_google_map ")]' ) as $tag ) {
			$dummy = $this->create_script();
			$dummy->setAttribute( 'qcode-google-maps', '1' );
			$tag->parentNode->insertBefore( $dummy, $tag );
		}

	}

	protected function is_match( $content, $src ) {
		return '1' === $this->tags->current()->getAttribute( 'qcode-google-maps' ) && parent::is_match( $content, $src );
	}
}
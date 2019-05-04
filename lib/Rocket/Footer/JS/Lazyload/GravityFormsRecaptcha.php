<?php


namespace Rocket\Footer\JS\Lazyload;


class GravityFormsRecaptcha extends LazyloadAbstract {

	protected $regex = '~gfRecaptchaPoller~';
	private $loaded = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag_content = $this->get_script_content();
		if ( ! $this->loaded ) {
			$tags = $this->get_script_collection();
			while ( $tags->valid() ) {
				$tag = $tags->current();
				$src = $tag->getAttribute( 'src' );
				if ( ! empty( $src ) ) {
					$src = rocket_add_url_protocol( $src );
				}
				if ( ( 'google.com' === parse_url( $src, PHP_URL_HOST ) || 'www.google.com' === parse_url( $src, PHP_URL_HOST ) ) && '/recaptcha/api.js' === parse_url( $src, PHP_URL_PATH ) ) {
					$tag_content = $this->get_tag_content( $tag ) . $tag_content;
					$tag_content .= $this->get_tag_content( $this->create_script( ';(function($) {$(document).trigger(\'gform_post_render\');})(jQuery);' ) );
					$tag->remove();
					break;
				}
				$tags->next();
			}
			$this->lazyload_script( $tag_content, 'gforms-recaptcha' );
			$this->loaded = true;
		} else {
			$this->tags->remove();
		}

		/** @var \DOMElement $tag */
		foreach ( $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ginput_recaptcha ")]' ) as $tag ) {
			$tag->setAttribute( 'data-lazy-widget', 'gforms-recaptcha' );
		}
	}
}

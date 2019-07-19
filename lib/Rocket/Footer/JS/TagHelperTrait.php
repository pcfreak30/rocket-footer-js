<?php


namespace Rocket\Footer\JS;


/**
 * Trait TagHelperTrait
 *
 * @package Rocket\Footer\JS
 */
trait TagHelperTrait {
	/**
	 * @param DOMElement $tag
	 */
	protected function inject_tag( $tag ) {
		$this->tags->current()->parentNode->insertBefore( $tag, $this->tags->current() );
		$this->tags->next();
	}

	/**
	 * @param DOMElement $tag
	 */
	protected function append_tag( $tag ) {
		$this->tags->current()->parentNode->appendChild( $tag );
	}

	/**
	 * @param string $content
	 * @param string $src
	 *
	 * @param bool   $content_document
	 *
	 * @return \Rocket\Footer\JS\DOMElement
	 */
	protected function create_script( $content = null, $src = null, $content_document = true ) {
		/** @var DOMElement $external_tag */
		$external_tag = $this->create_tag( 'script', ( null !== $content && '' !== $content ? rocket_footer_js()->util->encode_script( $content ) : $content ), $content_document );
		$external_tag->setAttribute( 'type', 'text/javascript' );
		if ( $src ) {
			$external_tag->setAttribute( 'src', $src );
		}

		return $external_tag;
	}

	/**
	 * @param string $type
	 * @param string $content
	 * @param bool   $content_document
	 *
	 * @return mixed
	 */
	protected function create_tag( $type, $content = null, $content_document = true ) {
		if ( $content_document ) {
			return $this->content_document->createElement( $type, $content );
		}

		return $this->document->createElement( $type, $content );
	}

	/**
	 * @param DOMElement $tag
	 *
	 * @return mixed
	 */
	protected function get_script_content( $tag = null ) {
		if ( ! $tag ) {
			$tag = $this->tags->current();
		}

		return rocket_footer_js()->util->maybe_decode_script( str_replace(
			[ "\n", "\r", '<script>//', '//</script>' ],
			[
				'',
				'',
				'<script>',
				'</script>',
			], $this->get_tag_content( $tag ) ) );
	}

	protected function get_tag_content( $tag = null ) {
		if ( ! $tag ) {
			$tag = $this->tags->current();
		}

		if ( ! ( $tag instanceof \DOMElement ) ) {
			return false;
		}

		return $tag->ownerDocument->saveHTML( $tag );
	}

	/**
	 * @param DOMElement $tag
	 *
	 * @return void
	 */
	protected function set_no_minify( $tag = null ) {
		if ( ! $tag ) {
			$tag = $this->tags->current();
		}

		if ( ! ( $tag instanceof \DOMElement ) ) {
			return;
		}

		$tag->setAttribute( 'data-no-minify', '1' );
	}

	/**
	 * @param DOMElement $tag
	 *
	 * @return mixed
	 */
	protected function is_no_minify( $tag = null ) {
		if ( ! $tag ) {
			$tag = $this->tags->current();
		}

		if ( ! ( $tag instanceof \DOMElement ) ) {
			return false;
		}

		return '1' === trim( $tag->getAttribute( 'data-no-minify' ) );
	}

	/**
	 * @param DOMElement $tag
	 *
	 * @return mixed
	 */
	protected function is_no_lazyload( $tag = null ) {
		if ( ! $tag ) {
			$tag = $this->tags->current();
		}

		if ( ! ( $tag instanceof \DOMElement ) ) {
			return false;
		}

		return '1' === trim( $tag->getAttribute( 'data-no-lazyload' ) );
	}

	/**
	 * @return DOMCollection
	 */
	protected function get_script_collection() {
		return $this->get_tag_collection( 'script' );
	}

	/**
	 * @param $type
	 *
	 * @return DOMCollection
	 */
	protected function get_tag_collection( $type ) {
		return rocket_footer_js_container()->create( '\\Rocket\\Footer\\JS\\DOMCollection', [
			$this->content_document,
			$type,
		] );
	}
}

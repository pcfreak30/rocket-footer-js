<?php


namespace Rocket\Footer\JS;


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
		$this->tags->current()->appendChild( $tag );
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
		if ( $content_document ) {
			$external_tag = $this->create_tag( 'script', $content, $content_document );
		} else {
			$external_tag = $this->create_tag( 'script', $content, $content_document );
		}
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

		return str_replace(
			[ "\n", "\r", '<script>//', '//</script>' ],
			[
				'',
				'',
				'<script>',
				'</script>',
			], $tag->ownerDocument->saveHTML( $tag ) );
	}
}
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
			$external_tag = $this->content_document->createElement( 'script', $content );
		} else {
			$external_tag = $this->document->createElement( 'script', $content );
		}
		$external_tag->setAttribute( 'type', 'text/javascript' );
		if ( $src ) {
			$external_tag->setAttribute( 'src', $src );
		}

		return $external_tag;
	}
}
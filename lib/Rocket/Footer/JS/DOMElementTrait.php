<?php

namespace Rocket\Footer\JS;

use DOMNode;

trait DOMElementTrait {
	/**
	 * @param \DOMNode $newnode
	 */
	public function appendChild( DOMNode $newnode ) {
		$doc = $this->ownerDocument;
		if ( $this instanceof DOMDocument ) {
			$doc = $this;
		}
		if ( $doc && ! $newnode->ownerDocument->isSameNode( $this ) ) {
			/** @var \Rocket\Footer\JS\DOMElement $newnode_imported */
			$newnode_imported         = $doc->importNode( $newnode, true );
			$map                      = rocket_footer_js()->get_node_map();
			$map[ $newnode_imported ] = $newnode;
			$newnode                  = $newnode_imported;
		}
		parent::appendChild( $newnode );
	}

	/**
	 *
	 */
	public function remove() {
		if ( $this->parentNode ) {
			$this->parentNode->removeChild( $this );
		}
	}

	public function next( $xpath_expr ) {
		$xpath      = new \DOMXPath( $this->ownerDocument );
		$xpath_expr = trim( "following-sibling::{$xpath_expr}", ':' );
		if ( ( $result = $xpath->query( $xpath_expr, $this ) ) && 0 < $result->length ) {
			return $result->item( 0 );
		}

		return false;
	}

	public function prev( $xpath_expr ) {
		$xpath      = new \DOMXPath( $this->ownerDocument );
		$xpath_expr = trim( "preceding-sibling::{$xpath_expr}", ':' );
		if ( ( $result = $xpath->query( $xpath_expr, $this ) ) && 0 < $result->length ) {
			return $result->item( 0 );
		}

		return false;
	}
}
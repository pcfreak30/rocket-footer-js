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
}
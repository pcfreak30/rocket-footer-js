<?php


namespace Rocket\Footer\JS;


class DOMDocument extends \DOMDocument {
	use DOMElementTrait;

	public function get_script_tags() {
		return $this->getElementsByTagName( 'script' );
	}

}
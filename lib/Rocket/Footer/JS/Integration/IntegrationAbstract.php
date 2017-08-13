<?php


namespace Rocket\Footer\JS\Integration;


use pcfreak30\WordPress\Plugin\Framework\ComponentAbstract;
use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\TagHelperTrait;

abstract class IntegrationAbstract extends ComponentAbstract {
	use TagHelperTrait;
	/**
	 * @var DOMDocument
	 */
	protected $content_document;
	/**
	 * @var DOMCollection
	 */
	protected $tags;
}
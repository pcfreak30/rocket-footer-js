<?php


namespace Rocket\Footer\JS\Integration;


use ComposePress\Core\Abstracts\Component;
use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\TagHelperTrait;

/**
 * Class IntegrationAbstract
 *
 * @package Rocket\Footer\JS\Integration
 * @property \Rocket\Footer\JS $plugin
 */
abstract class IntegrationAbstract extends Component {
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
<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\Rewrite\LazyloadInterface;
use Rocket\Footer\JS\TagHelperTrait;

/**
 * Class LazyloadAbstract
 *
 * @package Rocket\Footer\JS\LazyLoad
 */
abstract class LazyloadAbstract implements LazyloadInterface {
	use TagHelperTrait;
	/**
	 * @var DOMCollection
	 */
	protected $tags;
	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	protected $document;
	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	protected $content_document;
	/**
	 * @var \DOMXPath
	 */
	protected $xpath;

	/**
	 * @var int
	 */
	protected $instance = 0;

	/**
	 *
	 */
	public function init() {
		add_action( 'rocket_footer_js_do_lazyload', [ $this, 'lazyload' ] );
	}

	/**
	 * @param DOMDocument $document
	 * @param DOMDocument $content_document
	 */
	public function lazyload( $document = null, $content_document = null ) {
		if ( ! $document ) {
			$document = rocket_footer_js()->get_document();
		}
		if ( ! $content_document ) {
			$content_document = $document;
		}
		$this->document         = $document;
		$this->content_document = $content_document;
		$this->tags             = rocket_footer_js_container()->create( '\\Rocket\\Footer\\JS\\DOMCollection', [
			$this->content_document,
			'script',
		] );
		$this->xpath            = new \DOMXPath( $content_document );
		$this->before_do_lazyload();
		while ( $this->tags->valid() ) {
			$tag = $this->tags->current();
			$src = $tag->getAttribute( 'src' );
			if ( ! empty( $src ) ) {
				$src = rocket_add_url_protocol( $src );
			}
			$content = str_replace( [ "\n", "\r" ], '', $tag->textContent );
			$content = trim( $content, '/' );
			if ( ! $this->is_enabled() ) {
				$this->do_lazyload_off( $content, $src );
				$this->tags->next();
				continue;
			}
			$this->do_lazyload( $content, $src );
			$this->tags->next();
		}
	}

	/**
	 *
	 */
	protected function before_do_lazyload() {

	}

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	abstract protected function do_lazyload( $content, $src );


	/**
	 * @param string $content
	 * @param string $src
	 *
	 * @return void
	 */
	abstract protected function do_lazyload_off( $content, $src );


	/**
	 * @return bool
	 */
	protected function is_enabled() {
		return rocket_footer_js()->get_lazyload_manager()->is_enabled();
	}
}
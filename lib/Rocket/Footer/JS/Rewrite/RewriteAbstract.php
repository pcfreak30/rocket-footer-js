<?php


namespace Rocket\Footer\JS\Rewrite;

use Rocket\Footer\JS\DOMCollection;

/**
 * Class RewriteAbstract
 *
 * @package Rocket\Footer\JS\Rewrite
 */
abstract class RewriteAbstract implements RewriteInterface {

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
	 *
	 */
	public function init() {
		add_action( 'rocket_footer_js_do_rewrites', [ $this, 'rewrite' ] );
	}

	/**
	 * @param null $document
	 * @param null $content_document
	 */
	public function rewrite( $document = null, $content_document = null ) {
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
		$this->before_do_rewrite();
		while ( $this->tags->valid() ) {
			$tag     = $this->tags->current();
			$src     = $tag->getAttribute( 'src' );
			$src     = rocket_add_url_protocol( $src );
			$content = str_replace( [ "\n", "\r" ], '', $tag->textContent );
			$content = trim( $content, '/' );
			$this->do_rewrite( $content, $src );
			$this->tags->next();
		}
	}

	/**
	 *
	 */
	protected function before_do_rewrite() {

	}

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return mixed
	 */
	abstract protected function do_rewrite( $content, $src );
}
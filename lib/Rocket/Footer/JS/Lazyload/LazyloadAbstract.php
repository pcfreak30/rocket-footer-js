<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\DOMElement;
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

	protected $regex;

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
	protected function do_lazyload_off( $content, $src ) {
		if ( empty( $this->regex ) || preg_match( $this->regex, $content ) ) {
			$this->set_no_minify();
		}
	}


	/**
	 * @return bool
	 */
	protected function is_enabled() {
		return rocket_footer_js()->get_lazyload_manager()->is_enabled();
	}

	protected function lazyload_script( $html, $id, $tag = null ) {
		/** @var DOMElement $external_tag */
		if ( ! $tag ) {
			$tag = $this->tags->current();
		}
		if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
			$external_tag = $this->content_document->createElement( 'div' );
			$external_tag->appendChild( $this->content_document->createElement( 'WP_ROCKET_FOOTER_JS_LAZYLOAD_START' ) );
			$external_tag->appendChild( $this->content_document->createTextNode( $html ) );
			$external_tag->appendChild( $this->content_document->createElement( 'WP_ROCKET_FOOTER_JS_LAZYLOAD_END' ) );
		} else {
			$comment_tag  = $this->content_document->createComment( $html );
			$external_tag = $this->content_document->createElement( 'div' );
			$external_tag->appendChild( $comment_tag );
		}
		$external_tag->setAttribute( 'id', $id );
		if ( $this->content_document->isSameNode( $this->document ) ) {
			$tag->parentNode->insertBefore( $external_tag, $tag );
		} else {
			$this->content_document->getElementsByTagName( 'body' )->item( 0 )->appendChild( $external_tag );
		}
		$tag->parentNode->removeChild( $tag );
	}

	/**
	 * @return DOMElement
	 */
	protected function create_pixel_image() {
		$img = $this->create_tag( 'img' );
		$img->setAttribute( 'src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );

		return $img;
	}
}
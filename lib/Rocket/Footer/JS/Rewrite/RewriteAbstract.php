<?php


namespace Rocket\Footer\JS\Rewrite;

use ComposePress\Core\Abstracts\Component;
use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\TagHelperTrait;

/**
 * Class RewriteAbstract
 *
 * @package Rocket\Footer\JS\Rewrite
 * @property \Rocket\Footer\JS $plugin
 */
abstract class RewriteAbstract extends Component {

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
	 * @var string
	 */
	protected $regex;

	/**
	 *
	 */
	public function init() {
		add_action( 'rocket_footer_js_do_rewrites', [ $this, 'rewrite' ], 10, 2 );
	}

	/**
	 * @param null $document
	 * @param null $content_document
	 */
	public function rewrite( $document = null, $content_document = null ) {
		if ( ! $document ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$document = $this->plugin->document;
		}
		if ( ! $content_document ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$content_document = $document;
		}
		$this->document         = $document;
		$this->content_document = $content_document;
		$this->tags             = $this->get_script_collection();
		$this->xpath            = new \DOMXPath( $content_document );
		$this->before_do_rewrite();
		while ( $this->tags->valid() ) {
			$tag = $this->tags->current();
			if ( $this->is_no_minify() ) {
				$this->tags->next();
				continue;
			}
			$src = $tag->getAttribute( 'src' );
			if ( ! empty( $src ) ) {
				$src = rocket_add_url_protocol( $src );
			}
			$content = $tag->textContent;
			if ( empty( $src ) ) {
				$content = $this->plugin->util->maybe_decode_script( $content );
			}
			$content = str_replace( [ "\n", "\r" ], '', $content );
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
	 * @return void
	 */
	abstract protected function do_rewrite( $content, $src );

	/**
	 * @return string
	 */
	public function get_regex() {
		return $this->regex;
	}
}
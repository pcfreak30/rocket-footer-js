<?php


namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\TagHelperTrait;

class BlogHer implements IntegrationInterface {
	use TagHelperTrait;
	/**
	 * @var DOMDocument
	 */
	protected $content_document;
	/**
	 * @var DOMCollection
	 */
	protected $tags;

	private $injected = false;


	public function init() {
		add_filter( 'rocket_footer_js_process_remote_script', [ $this, 'process' ] );
	}

	public function process( $script, $url ) {
		if ( ! $this->injected && 'ads.blogherads.com' === parse_url( $url, PHP_URL_HOST ) ) {
			$this->content_document = rocket_footer_js()->get_script_document();
			$this->tags             = rocket_footer_js()->get_dom_collection();
			if ( false !== strpos( $script, 'static/blogherads.js' ) ) {
				$file = rocket_footer_js()->remote_fetch( $url );
				if ( ! empty( $file ) ) {
					$script         = $file . $script;
					$this->injected = true;
				}
			}
		}

		return $script;
	}
}
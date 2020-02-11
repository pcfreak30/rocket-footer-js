<?php


namespace Rocket\Footer\JS\Lazyload;


/**
 * Class HubSpotForms
 *
 * @package Rocket\Footer\JS\Lazyload
 * @property \Rocket\Footer\JS $plugin
 */
class HubSpotForms extends LazyloadAbstract {

	/**
	 * @var string
	 */
	protected $regex = '~hbspt\s*\.\s*forms~';

	/**
	 * @var bool
	 */
	protected $framework_removed = false;
	/**
	 * @var string
	 */
	protected $framework_url = '//js.hsforms.net/forms/v2.js';

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		$tag = $this->tags->current();


		if ( ! $this->framework_removed ) {
			$nodes = $this->xpath->query( '//script[contains(@src, "js.hsforms.net")]' );
			if ( 0 < $nodes->length ) {
				$this->framework_url = $nodes->item( 0 )->getAttribute( 'src' );
			}
			/** @var \Rocket\Footer\JS\DOMElement $node */
			foreach ( $nodes as $node ) {
				$node->remove();
			}
			$this->framework_removed = true;
		}

		$script_content = $this->get_script_content( $this->create_script( null, $this->framework_url ) );

		$inline_tag = $this->create_script( '(function(){
				(function timer(){
						if("hbspt" in window){
							' . $content . ';
							return;
						}
						setTimeout(timer, 10);
				})();
					
			})();' );

		$script_content .= $this->get_script_content( $inline_tag );

		$external_tag = $this->lazyload_script( $script_content, "hubspot-form-{$this->instance}", $tag );
		$img          = $this->create_pixel_image();
		$img->setAttribute( 'data-lazy-widget', "hubspot-form-{$this->instance}" );
		$external_tag->appendChild( $img );
		$this->instance ++;

		$tag->remove();
		$this->tags->flag_removed();
		$this->tags->rewind();
	}

	/**
	 * @param string $content
	 * @param string $src
	 */
	protected function do_lazyload_off( $content, $src ) {
		$this->set_no_minify();

		$nodes = $this->xpath->query( '//script[contains(@src, "js.hsforms.net")]' );
		/** @var \Rocket\Footer\JS\DOMElement $node */
		foreach ( $nodes as $node ) {
			$this->set_no_minify( $node );
		}
	}
}



<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMElement;

class StumbleUpon extends LazyloadAbstract {

	protected $regex = '~\(\s*function\s*\(\s*\)\s*{.*[\'"]((?:https?:)?//platform\.stumbleupon\.com/1/widgets.js)[\'"].*\}\s*\)\s*\(\s*\)\s*;~';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( preg_match( $this->regex, $content, $matches ) ) {
			$tag_content = $this->get_script_content();
			$this->lazyload_script( $tag_content, 'stumbleupon' );
			/** @var DOMElement $tag */
			foreach ( $this->get_tag_collection( 'su:badge' ) as $tag ) {
				$tag->setAttribute( 'data-lazy-widget', 'stumbleupon' );
			}
			foreach ( $this->get_tag_collection( 'su:follow' ) as $tag ) {
				$tag->setAttribute( 'data-lazy-widget', 'stumbleupon' );
			}
		}
	}
}
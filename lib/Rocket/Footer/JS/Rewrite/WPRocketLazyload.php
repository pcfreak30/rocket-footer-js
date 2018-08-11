<?php


namespace Rocket\Footer\JS\Rewrite;


class WPRocketLazyload extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\(function\(\s*w\s*,\s*d\s*\)\s*{\s*.*b\.src\s*=\s*"(.*)"\s*;.*\(\s*window,document\s*\)\s*;~s', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( null, $matches[1] ) );
			$this->tags->remove();

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );
		}
	}
}
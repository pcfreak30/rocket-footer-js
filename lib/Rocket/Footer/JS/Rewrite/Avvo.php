<?php


namespace Rocket\Footer\JS\Rewrite;


class Avvo extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{\s*setTimeout\s*\(\s*function\s*\(\s*\){\s*var\s*s\s*=\s*.*s\s*\.\s*src="((?:https?:)?//ia\s*.avvo\s*.com/tracker/\w+.js)".*}\s*\)\s*\(\s*\)\s*;~s', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( null, $matches[1] ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
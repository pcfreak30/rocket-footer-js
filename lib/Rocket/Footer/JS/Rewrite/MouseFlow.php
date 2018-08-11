<?php


namespace Rocket\Footer\JS\Rewrite;


class MouseFlow extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{\s*.*mf\s*\.\s*src\s*=\s*"((?:https?:)?//cdn\.mouseflow\.com/projects/[\w-]+\.js)".*}\s*\)\s*\(\s*\)\s*;~s', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( 'var _mfq = _mfq || [];' ) );
			$this->inject_tag( $this->create_script( null, $matches[1] ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
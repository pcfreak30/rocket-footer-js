<?php


namespace Rocket\Footer\JS\Rewrite;


class PushCrew extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\!?\(\s*function\s*\(\s*p\s*,\s*u\s*,\s*s\s*,\s*h\s*\)\{\s*.*\'((?:https?:)?//cdn\.pushcrew\.com/js/\w+\.js)\'.*\}\s*\)\s*\(\s*window\s*,\s*document\s*\);~s', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( "(function(p,u){p._pcq=p._pcq||[];p._pcq.push(['_currentTime',Date.now()]);})(window);" ) );
			$this->inject_tag( $this->create_script( null, $matches[1] ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
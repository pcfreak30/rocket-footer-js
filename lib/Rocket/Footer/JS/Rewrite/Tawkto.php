<?php


namespace Rocket\Footer\JS\Rewrite;


class Tawkto extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~var\s*Tawk_API\s*=\s*Tawk_API.*s1.src\s*=\s*\'(.*)\';.*s0\.parentNode\.insertBefore\(s1,s0\);\s*}\s*\)\(\);~sU', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( null, $matches[1] ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );
			$this->tags->remove();
		}
	}
}
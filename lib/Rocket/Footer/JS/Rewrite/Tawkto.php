<?php


namespace Rocket\Footer\JS\Rewrite;


class Tawkto extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @return mixed
	 */
	protected function do_rewrite( $content ) {
		if ( preg_match( '~var\s*Tawk_API\s*=\s*Tawk_API.*s1.src\s*=\s*\'(.*)\';.*s0\.parentNode\.insertBefore\(s1,s0\);\s*}\s*\)\(\);~sU', $content, $matches ) ) {
			$external_tag = $this->document->createElement( 'script' );
			$external_tag->setAttribute( 'type', 'text/javascript' );
			$external_tag->setAttribute( 'src', "{$matches[1]}" );
			$external_tag->setAttribute( 'async', false );
			$this->tags->current()->parentNode->insertBefore( $external_tag, $this->tags->current() );
			$this->tags->remove();
		}
	}
}
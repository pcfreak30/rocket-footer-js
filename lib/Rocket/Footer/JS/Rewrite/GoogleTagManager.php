<?php


namespace Rocket\Footer\JS\Rewrite;


class GoogleTagManager extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\(\s*function\s*\(\s*w\s*,\s*\s*d,\s*s,\s*l,\s*i\s*\)\s*\{\s*w\s*\[\s*l\s*\]\s*=\s*w\s*\[\s*l\]\s*\|\|\s*\[\s*\]\s*;\s*w\s*\[\s*l\s*\].*\(\s*window\s*,\s*document\s*,\s*\'script\'\s*,\s*\'.*\'\s*,\s*\'(GTM-TPLZ9H)\'\s*\);~s', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( null, add_query_arg( 'id', $matches[1], 'https://www.googletagmanager.com/gtm.js' ) ) );
			$this->tags->remove();
		}
	}
}
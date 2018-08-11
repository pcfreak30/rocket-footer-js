<?php


namespace Rocket\Footer\JS\Rewrite;


class Clicky extends RewriteAbstract {

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~var\s*clicky_site_ids\s*=\s*clicky_site_ids\s*\|\|\s*\[\s*\]\s*;\s*clicky_site_ids\.push\s*\((\d+)\s*\)\;\s*\(\s*function\s*\(\s*\)\s*\{.*s\s*\.\s*src\s*=\s*\'((?:https?:)?//static\.getclicky\.com/js)\';.*}\s*\)\s*\(\s*\)\s*;~s', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( "var clicky_site_ids = clicky_site_ids || [];clicky_site_ids.push({$matches[1]});" ) );
			$this->inject_tag( $this->create_script( null, $matches[2] ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
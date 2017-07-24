<?php


namespace Rocket\Footer\JS\Rewrite;


class GoogleWebFonts extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~(WebFontConfig\s*=\s{.*};)?\s*\(\s*function\s*\(\s*\)\s*{\s*var\s*wf\s*=\s*document\s*\.\s*createElement\s*\(\s*\'script\'\s*\)\s*;.*s\s*.\s*parentNode\s*.insertBefore\s*\(\s*wf\s*,\s*s\)\s*;\s*}\s*\)\s*\(\s*\);~s', $content, $matches ) ) {

			$this->inject_tag( $this->create_script( $matches[1] ) );
			$this->inject_tag( $this->create_script( null, rocket_add_url_protocol( '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js' ) ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			if ( ! empty( $content ) ) {
				$this->inject_tag( $this->create_script( $content ) );
			}

			$this->tags->remove();
		}
	}
}
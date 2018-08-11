<?php


namespace Rocket\Footer\JS\Rewrite;


class DoubleClickGoogleAnalytics extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\(\s*function\s*\(\s*\)\s*{\s*var\s*ga\s*=\s*.*\s\'stats\.g\.doubleclick\.net/dc\.js\'.*s\s*.\s*parentNode\s*.\s*insertBefore\s*\(\s*ga\s*,\s*s\);\s*}\s*\)\s*\(\s*\);~', $content, $matches ) ) {
			preg_match_all( '~_gaq\s*\.\s*push\s*.*;~U', $content, $gaq_calls );
			$gaq_calls = call_user_func_array( 'array_merge', $gaq_calls );
			$this->inject_tag( $this->create_script( 'var _gaq = _gaq || [];' . implode( "\n", $gaq_calls ) ) );
			$this->inject_tag( $this->create_script( null, '//stats.g.doubleclick.net/dc.js' ) );
			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
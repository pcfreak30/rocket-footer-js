<?php


namespace Rocket\Footer\JS\Rewrite;


class BingBat extends RewriteAbstract {
	protected $regex = '~\(\s*function\s*\(\s*w\s*,\s*d\s*,\s*t\s*,\s*r\s*,\s*u\s*\)\s*\{var\s*f\s*,\s*n\s*,\s*i\s*;\s*w\s*\[\s*u\s*\]\s*\=\s*\w\s*\[u\s*\].*ti:[\'"](\d+)[\'"]\}.*\s*\(\s*window\s*,\s*document\s*,\s*"script"\s*,\s*"\/\/bat\.bing\.com\/bat\.js"\s*,\s*[\'"](.*)[\'"]\s*\)\s*;~sU';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( $this->regex, $content, $matches ) ) {
			$this->inject_tag( $this->create_script( null, 'https://bat.bing.com/bat.js' ) );
			$this->inject_tag( $this->create_script( '(function(w, u){w[u] = w[u] || []; var o = {ti: "' . $matches[1] . '"};o.q = w[u], w[u] = new UET(o), w[u].push("pageLoad")})(window, "' . $matches[2] . '");' ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
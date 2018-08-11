<?php


namespace Rocket\Footer\JS\Rewrite;


class GoogleAnalytics extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\\(function\s*\(\s*i\s*,\s*s\s*,\s*o\s*,\s*g\s*,\s*r\s*,\s*a\s*,\s*m\s*\)\s*{\s*i\[\'GoogleAnalyticsObject\'\]\s*=\s*r;\s*i\[r\]\s*=\s*i\[r\]\s*\|\|\s*function\s*\(\)\s*\{.*\'(.*//(?:www\.)?google-analytics\.com/analytics\.js)\'\s*,\s*\'(?:ga|__gaTracker)\'\s*\);~', $content, $matches ) || preg_match( '~\(function\s*\(\s*\) {\s*var\s*ga\s*=\s*document\s*\.\s*createElement.*\.google-analytics\.com/ga\.js.*\}\s*\)\s*\(\s*\);~', $content, $matches ) ) {
			preg_match_all( '~ga\s*\(\s*.*\s*\)\s*;~U', $content, $ga_calls );
			$ga_calls = call_user_func_array( 'array_merge', $ga_calls );
			if ( empty( $matches[1] ) ) {
				$matches[1] = ( is_ssl() ? 'https://ssl' : 'http://www' ) . '.google-analytics.com/ga.js';
			}
			$this->inject_tag( $this->create_script( 'window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date; ' . implode( "\n", $ga_calls ) ) );
			$this->inject_tag( $this->create_script( null, $matches[1] ) );
			$content = trim( str_replace( $matches[0], '', $content ) );
			$content = trim( str_replace( $ga_calls, '', $content ) );


			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
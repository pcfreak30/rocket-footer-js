<?php


namespace Rocket\Footer\JS\Rewrite;


class Klaviyo extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~\(function\(\s*\)\s*{\s*.*pa\s*.src\s*=\s*"//a\s*.klaviyo\s*\.com/media/js/analytics/analytics\s*\.js";.*\s*\}\s*\)\s*\(\s*\)\s*;~s', $content, $matches ) ) {
			$sub_content = trim( str_replace( $matches[0], '', $content ) );
			if ( ! empty( $sub_content ) ) {
				$this->inject_tag( $this->create_script( $sub_content ) );
			}
			$this->inject_tag( $this->create_script( null, 'https://a.klaviyo.com/media/js/analytics/analytics.js' ) );
			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
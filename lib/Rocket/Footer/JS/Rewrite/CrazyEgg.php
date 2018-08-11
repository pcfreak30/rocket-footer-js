<?php


namespace Rocket\Footer\JS\Rewrite;


class CrazyEgg extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( '~//script\.crazyegg\.com(/pages/scripts/\d+/\d+\.js)~', $content, $matches ) ) {
			$this->inject_tag( $this->create_script( null, "https://script.crazyegg.com{$matches[1]}" ) );

			$content = trim( str_replace( $matches[0], '', $content ) );
			$this->inject_tag( $this->create_script( $content ) );

			$this->tags->remove();
		}
	}
}
<?php


namespace Rocket\Footer\JS\Rewrite;


class CallRail extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( 'cdn.callrail.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$this->set_no_minify();
		}
	}
}
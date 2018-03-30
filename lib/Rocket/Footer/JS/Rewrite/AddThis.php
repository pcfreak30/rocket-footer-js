<?php


namespace Rocket\Footer\JS\Rewrite;


class AddThis extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( false !== strpos( parse_url( $src, PHP_URL_HOST ), 'addthis.com' ) ) {
			$this->set_no_minify();
		}
	}
}
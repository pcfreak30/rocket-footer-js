<?php


namespace Rocket\Footer\JS\Rewrite;


class Shareaholic extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		$tag = $this->tags->current();
		if ( 'apps.shareaholic.com' === parse_url( $src, PHP_URL_HOST ) && '' !== $tag->getAttribute( 'data-shr-siteid' ) ) {
			$this->set_no_minify();
		}
	}
}
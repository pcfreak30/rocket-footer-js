<?php


namespace Rocket\Footer\JS\Rewrite;


class StripeJS extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		$tag = $this->tags->current();
		if ( 'js.stripe.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$this->set_no_minify();
		}
	}
}
<?php


namespace Rocket\Footer\JS\Rewrite;


class SumoMe extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		$tag = $this->tags->current();
		if ( 'load.sumome.com' === parse_url( $src, PHP_URL_HOST ) && '' !== $tag->getAttribute( 'data-sumo-site-id' ) ) {
			$tag->removeAttribute( 'src' );
			$this->set_no_minify();
			$this->inject_tag( $this->create_script( null, $src ) );
		}
	}
}
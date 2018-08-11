<?php


namespace Rocket\Footer\JS\Rewrite;


class CookieBot extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		$tag = $this->tags->current();
		if ( 'consent.cookiebot.com' === parse_url( $src, PHP_URL_HOST ) && '' !== $tag->getAttribute( 'data-cbid' ) ) {
			$tag->removeAttribute( 'src' );
			$this->set_no_minify();

			$override_src = <<<JS
			(function(){
				var script = document.getElementById('Cookiebot');
				if(!script){
					return;
				}
				if(!Object.defineProperty){
					script.src = '$src';
					return;
				}
				Object.defineProperty(script, 'src', {get: function () {
                    return window.location.protocol   +'//' + 'consent.cookiebot.com';
                }});
			})();
JS;

			$this->inject_tag( $this->create_script( $override_src ) );
			$this->inject_tag( $this->create_script( null, $src ) );

		}
		if ( 'consent.cookiebot.com' === parse_url( $src, PHP_URL_HOST ) && 'cd.js' === pathinfo( $src, PATHINFO_BASENAME ) ) {
			$this->set_no_minify();
		}
	}
}
<?php


namespace Rocket\Footer\JS\Rewrite;


class YouTubeEmbedAPI extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( ( 'www.youtube.com' === parse_url( $src, PHP_URL_HOST ) || 'youtube.com' === parse_url( $src, PHP_URL_HOST ) ) &&
		     ( '/iframe_api' === parse_url( $src, PHP_URL_PATH ) || '/player_api' === parse_url( $src, PHP_URL_PATH ) ) ) {
			$file = $this->plugin->remote_fetch( $src );
			if ( ! empty( $file ) ) {
				if ( preg_match( '~a\s*\.\s*src\s*=\s*\'(.*)\'\s*;~s', $file, $matches ) ) {
					$this->inject_tag( $this->create_script( 'if(!window["YT"])var YT={loading:0,loaded:0};if(!window["YTConfig"])var YTConfig={"host":"http://www.youtube.com"};YT.loading=1; (function() {var l = [];YT.ready=function(f){if(YT.loaded)f();else l.push(f)};window.onYTReady=function(){YT.loaded=1;for(var i=0;i<l.length;i++)try{l[i]()}catch(e){}};YT.setConfig=function(c){for(var k in c)if(c.hasOwnProperty(k))YTConfig[k]=c[k]};})();', false ) );
					$dummy_script = $this->create_script( null, null );
					$dummy_script->setAttribute( 'id', 'www-widgetapi-script' );

					$this->set_no_minify( $dummy_script );
					$this->inject_tag( $dummy_script );
					$this->inject_tag( $this->create_script( null, $matches[1], false ) );

					$this->tags->remove();
				}
			}
		}
	}
}
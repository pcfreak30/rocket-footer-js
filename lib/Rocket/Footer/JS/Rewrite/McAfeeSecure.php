<?php


namespace Rocket\Footer\JS\Rewrite;


class McAfeeSecure extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( ( 'cdn.ywxi.net' === parse_url( $src, PHP_URL_HOST ) && '/js/1.js' === parse_url( $src, PHP_URL_PATH ) ) || false !== strpos( $content, '.ywxi.net/js/1.js' ) ) {
			$file = $this->plugin->remote_fetch( add_query_arg( 'h', parse_url( home_url(), PHP_URL_HOST ), 'https://cdn.ywxi.net/js/host-loader.js' ) );
			if ( ! empty( $file ) && preg_match( '~host.js\?v=\d+&h=[\w\.-]+~', $file, $matches ) ) {
				$external_script = $this->create_script( null, "https://cdn.ywxi.net/js/{$matches[0]}" );
				$this->inject_tag( $external_script );
				$this->tags->remove();
			}
		}
	}
}
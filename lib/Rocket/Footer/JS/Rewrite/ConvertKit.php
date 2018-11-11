<?php


namespace Rocket\Footer\JS\Rewrite;


class ConvertKit extends RewriteAbstract {

	private $injected = [];
	private $injected_id = [];

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		$tag = $this->tags->current();
		if ( 'forms.convertkit.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$this->set_no_minify();
			$tag->removeAttribute( 'src' );

			$file = $this->plugin->remote_fetch( $src );
			if ( preg_match( '~\(function\s*\(\s*d\s*,\s*id\s*,\s*h\s*,\s*a\s*\)\s*\{.*a\s*\.src\s*=\s*["\'](.*)["\']\s*;.*}\s*\)\s*\(\s*\s*document\s*,\s*[\'"](.*)["\']\s*\)\s*;~sU', $file, $matches ) ) {
				if ( ! $this->injected[ $matches[1] ] && ! $this->injected_id[ $matches[2] ] ) {
					$this->inject_tag( $this->create_script( null, $matches[1] ) );
					$script = $this->create_script();
					$script->setAttribute( 'id', $matches[2] );
					$this->set_no_minify( $script );
					$this->inject_tag( $script );
					$this->injected[ $matches[1] ]    = true;
					$this->injected_id[ $matches[2] ] = true;
				}
			}

			$this->inject_tag( $this->create_script( null, $src ) );
		}
	}
}

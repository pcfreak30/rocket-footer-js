<?php


namespace Rocket\Footer\JS\Rewrite;


class TypeKit extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( 'use.typekit.net' === parse_url( $src, PHP_URL_HOST ) ) {
			$file = $this->plugin->remote_fetch( $src );
			if ( ! empty( $file ) && preg_match( '~window\.Typekit\.config\s*=\s*({.*});~', $file, $matches ) ) {
				$json                 = json_decode( $matches[1] );
				$formats_list         = [ 'woff2', 'woff', 'eot', 'opentype' ];
				$formats_list_map     = [ 'woff2' => 'l', 'woff' => 'd', 'eot' => 'i', 'opentype' => 'a' ];
				$formats_list_ext_map = [ 'woff2' => 'woff2', 'woff' => 'woff', 'eot' => 'eot', 'opentype' => 'otf' ];
				$formats_index_list   = array_flip( $formats_list );
				if ( ! empty( $json->fc ) ) {
					$head    = $this->document->getElementsByTagName( 'head' )->item( 0 );
					$all_css = '';
					foreach ( $json->fc as $index => $font ) {
						$formats = [];
						$src     = $font->src;
						if ( preg_match_all( '~{\??(.*)}~U', $src, $matches ) ) {
							$args_list  = array_map( 'trim', explode( ',', $matches[1][1] ) );
							$args_list  = array_map( function ( $item ) {
								return trim( $item, '?' );
							}, $args_list );
							$query_args = [];
							foreach ( $args_list as $arg ) {
								switch ( $arg ) {
									case 'primer':
									case 'subset_id':
										if ( isset( $font->descriptors->$arg ) ) {
											$query_args[ $arg ] = $font->descriptors->$arg;
										}
										break;
									case 'fwd':
										$query_args[ $arg ] = $json->fn->${$font->family}[ $index ];
										break;
									case 'v':
										$query_args[ $arg ] = 3;
										break;

								}
							}
							$src = str_replace( $matches[0][1], '', $src );
							foreach ( $formats_list as $format ) {
								$new_src     = str_replace( $matches[0][0], $formats_list_map[ $format ], $src );
								$new_src     = add_query_arg( $query_args, $new_src );
								$fetched_src = $this->plugin->util->download_remote_file( $new_src, $formats_list_ext_map[ $format ] );
								if ( $fetched_src !== $new_src ) {
									$formats[ $format ] = $fetched_src;
								}
							}
							$css = '';
							if ( ! empty( $formats ) ) {
								$css = "@font-face{font-family:{$font->family};src:";
								foreach ( $formats as $format => $url ) {
									$css .= "url('{$url}') format(\"{$format}\")";
									if ( $formats_index_list[ $format ] != count( $formats_index_list ) - 1 ) {
										$css .= ',';
									}
								}
								$css .= ";font-weight: {$font->descriptors->weight}; font-style:{$font->descriptors->style}; font-display:swap; }";
							}
							$all_css .= $css;

						}
					}
					if ( ! empty( $all_css ) ) {
						$style_tag = $this->create_tag( 'style', $all_css );
						$style_tag->setAttribute( 'type', 'text/css' );
						$head->appendChild( $style_tag );
						$classes    = array_filter( explode( ' ', $this->document->documentElement->getAttribute( 'class' ) ) );
						$classes [] = 'wf-active';
						$classes    = array_unique( $classes );
						$this->document->documentElement->setAttribute( 'class', implode( ' ', $classes ) );
					}

					$content = trim( str_replace( $matches[0], '', $content ) );
					$this->inject_tag( $this->create_script( $content ) );

					$this->tags->remove();
				}
			}
		}
	}
}

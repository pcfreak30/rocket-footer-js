<?php


namespace Rocket\Footer\JS\Integration;


class Audio extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_filter( 'rocket_buffer', [ $this, 'process' ], 12 );
	}

	public function process( $html ) {
		if ( is_preview() || empty( $html ) ) {
			return $html;
		}

		$zone = array(
			'all',
		);

		if ( $cnames = get_rocket_cdn_cnames( $zone ) ) {

			/**
			 * Filters the filetypes allowed for the CDN
			 *
			 * @since  2.9
			 * @author Remy Perona
			 *
			 * @param array $filetypes Array of file types.
			 */
			$filetypes = apply_filters( 'rocket_cdn_custom_filetypes', array(
				'mp3',
				'ogg',
				'mp4',
				'm4v',
				'avi',
				'mov',
				'flv',
				'swf',
				'webm',
				'pdf',
				'doc',
				'docx',
				'txt',
				'zip',
				'tar',
				'bz2',
				'tgz',
				'rar',
				'jpg',
				'jpeg',
				'jpe',
				'png',
				'gif',
				'webp',
				'bmp',
				'tiff',
			) );
			$filetypes = implode( '|', $filetypes );

			preg_match_all( '#<(?:audio|source)[^>]+?src=[\'"]?([^"\'>]+\.(?:' . $filetypes . '))[\'"]?[^>]*>#i', $html, $matches );

			if ( (bool) $matches ) {
				$i = 0;
				foreach ( $matches[1] as $url ) {
					$url = trim( $url, " \t\n\r\0\x0B\"'" );
					$url = get_rocket_cdn_url( $url, $zone );
					$src = str_replace( $matches[1][ $i ], $url, $matches[0][ $i ] );
					$html = str_replace( $matches[0][ $i ], $src, $html );
					$i ++;
				}
			}
		}

		return $html;
	}
}
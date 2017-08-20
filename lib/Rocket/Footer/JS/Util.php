<?php


namespace Rocket\Footer\JS;


use pcfreak30\WordPress\Plugin\Framework\ComponentAbstract;

class Util extends ComponentAbstract {

	/**
	 *
	 */
	public function init() {
		// TODO: Implement init() method.
	}

	public function download_remote_file( $url ) {
		$data = $this->plugin->remote_fetch( $url );
		if ( ! empty( $data ) ) {
			$url_parts = parse_url( $url );
			$info      = pathinfo( $url_parts['path'] );
			if ( empty( $url_parts['port'] ) ) {
				$url_parts['port'] = '';
			}
			if ( empty( $info['extension'] ) ) {
				$tempfile = wp_tempnam();

				$this->plugin->get_wp_filesystem()->put_contents( $tempfile, $data );
				$filetype = wp_check_filetype_and_ext( $tempfile, basename( $tempfile ) );
				$filetype = array_filter( $filetype );
				$this->plugin->get_wp_filesystem()->delete( $tempfile );
				if ( empty( $filetype ) ) {
					return $url;
				}
				$info['extension'] = $filetype['ext'];
			}
			$hash      = md5( $url_parts['scheme'] . '://' . $info['dirname'] . ( ! empty( $url_parts['port'] ) ? ":{$url_parts['port']}" : '' ) . '/' . $info['filename'] );
			$filename  = $this->plugin->get_cache_path() . $hash . '.' . $info['extension'];
			$final_url = get_rocket_cdn_url( set_url_scheme( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $filename ) ) );
			if ( ! $this->plugin->get_wp_filesystem()->is_file( $filename ) ) {
				$this->plugin->put_content( $filename, $data );
			}
			$url = $final_url;
		}

		return $url;
	}
}
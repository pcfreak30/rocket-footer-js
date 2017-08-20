<?php


namespace Rocket\Footer\JS;


use Mimey\MimeTypes;
use pcfreak30\WordPress\Plugin\Framework\ComponentAbstract;

/**
 * Class Util
 *
 * @package Rocket\Footer\JS
 */
class Util extends ComponentAbstract {

	/**
	 *
	 */
	public function init() {
		// TODO: Implement init() method.
	}

	/**
	 * @param        $url
	 * @param string $extension
	 *
	 * @return string
	 */
	public function download_remote_file( $url, $extension = null ) {
		$data = $this->plugin->remote_fetch( $url );
		if ( ! empty( $data ) ) {
			$url_parts = parse_url( $url );
			$info      = pathinfo( $url_parts['path'] );
			if ( empty( $url_parts['port'] ) ) {
				$url_parts['port'] = '';
			}
			if ( empty( $info['extension'] ) ) {
				$info['extension'] = $extension;
			}
			if ( empty( $info['extension'] ) ) {
				return $url;
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
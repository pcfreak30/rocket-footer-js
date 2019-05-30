<?php


namespace Rocket\Footer\JS;


use ComposePress\Core\Abstracts\Component;

/**
 * Class Util
 *
 * @package Rocket\Footer\JS
 * @property \Rocket\Footer\JS $plugin
 */
class Util extends Component {

	/**
	 *
	 */
	public function init() {
		// TODO: Implement init() method.
	}

	/**
	 * @param array|string $urls
	 * @param string       $extension
	 *
	 * @return string
	 */
	public function download_remote_file( $urls, $extension = null, $cdn = true ) {
		foreach ( (array) $urls as $url ) {
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
				$final_url = set_url_scheme( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $filename ) );
				if ( $cdn ) {
					$final_url = get_rocket_cdn_url( $final_url );
				}

				if ( ! $this->plugin->get_wp_filesystem()->is_file( $filename ) ) {
					$this->plugin->put_content( $filename, $data );
				}

				return $final_url;
			}

		}

		return false;
	}

	public function maybe_decode_script( $data ) {
		if ( $this->is_base64_encoded( $data ) ) {
			return json_decode( base64_decode( $data ) );
		}

		return $data;
	}

	protected function is_base64_encoded( $data ) {
		if ( base64_decode( $data, true ) && json_decode( base64_decode( $data ) ) ) {
			return true;
		}

		return false;
	}

	public function encode_script( $data ) {
		return base64_encode( json_encode( $data ) );
	}
}

<?php


namespace Rocket\Footer\JS\Lazyload;


class Videos extends LazyloadAbstract {
	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {

	}

	protected function after_do_lazyload() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$oembed = _wp_oembed_get_object();
		$tags   = $this->get_tag_collection( 'iframe' );
		foreach ( $tags as $tag ) {
			if ( $this->is_no_lazyload( $tag ) ) {
				continue;
			}
			$src = $tag->getAttribute( 'data-src' );
			if ( empty( $src ) ) {
				$src = $tag->getAttribute( 'src' );
			}
			$src  = $this->maybe_translate_url( $src );
			$info = $oembed->get_data( $src );
			if ( ! empty( $info ) && 'video' === $info->type ) {
				$img = $this->create_tag( 'img' );
				$img->setAttribute( 'data-src', $this->plugin->util->download_remote_file( $info->thumbnail_url ) );
				$img->setAttribute( 'width', $info->thumbnail_width );
				$img->setAttribute( 'style', 'max-width:100%;height:auto;cursor:pointer;' );
				$img->setAttribute( 'data-lazy-video-embed', "lazyload-video-{$this->instance}" );
				$tag->parentNode->insertBefore( $img, $tag );
				$this->lazyload_script( $this->get_tag_content( $tag ), "lazyload-video-{$this->instance}", $tag );
				$tags->flag_removed();
				$this->instance ++;
			}
		}
	}

	/**
	 * @param string $url
	 */
	private function maybe_translate_url( $url ) {
		$url = parse_url( $url );
		if ( 'youtube.com' === $url['host'] || 'www.youtube.com' === $url['host'] ) {
			if ( false !== strpos( $url['path'], 'embed' ) ) {
				$video_id     = pathinfo( $url['path'], PATHINFO_FILENAME );
				$url['path']  = '/watch';
				$url['query'] = http_build_query( [ 'v' => $video_id ] );
			}
		}
		$url = http_build_url( $url );

		return $url;
	}


	protected function is_match( $content, $src ) {
		return false;
	}
}
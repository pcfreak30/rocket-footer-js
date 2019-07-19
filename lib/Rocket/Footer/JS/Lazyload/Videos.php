<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

/**
 * Class Videos
 *
 * @package Rocket\Footer\JS\Lazyload
 */
class Videos extends LazyloadAbstract {

	/**
	 * @var array
	 */
	private $srcsets = [];
	/**
	 * @var string
	 */
	private $srcset_attr;
	/**
	 * @var string
	 */
	private $sizes_attr;

	/**
	 * @param $upload_dir
	 *
	 * @return mixed
	 */
	public function modify_upload_dir( $upload_dir ) {
		$upload_dir['basedir'] = $this->plugin->get_cache_path();
		$upload_dir['baseurl'] = site_url( str_replace( ABSPATH, '/', $this->plugin->get_cache_path() ) );

		return $upload_dir;
	}

	/**
	 * @param $srcsets
	 *
	 * @return mixed
	 */
	public function save_srcsets( $srcsets ) {

		foreach ( $srcsets as $key => $srcset ) {
			$srcsets[ $key ]['url'] = get_rocket_cdn_url( $srcsets[ $key ]['url'], [
				'css',
				'js',
				'css_and_js',
				'all',
			] );
		}


		$this->srcsets = $srcsets;

		return $srcsets;
	}

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {

	}

	/**
	 *
	 */
	protected function after_do_lazyload() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$a3_lazy_load_global_settings = $this->a3_lazy_load_global_settings;
		if ( ! $a3_lazy_load_global_settings['a3l_apply_to_videos'] ) {
			return;
		}
		$oembed = _wp_oembed_get_object();
		$tags   = $this->get_tag_collection( 'iframe' );
		foreach ( $tags as $tag ) {
			if ( $this->is_no_lazyload( $tag ) ) {
				continue;
			}
			$src      = $tag->getAttribute( 'data-src' );
			$data_src = true;
			if ( empty( $src ) ) {
				$src      = $tag->getAttribute( 'src' );
				$data_src = false;
			}
			$original_src = $src;
			$src          = $this->maybe_translate_url( $src );
			$info         = $oembed->get_data( $src );

			$classes = $tag->getAttribute( 'class' );
			$classes = explode( ' ', $classes );
			$classes = array_map( 'trim', $classes );
			$classes = array_filter( $classes );

			$classes [] = 'lazyloaded-video';

			$no_lazyload_thumbnail = '1' === $tag->getAttribute( 'data-no-lazyload-thumbnail' );

			$tag->setAttribute( ( $data_src ? 'data-' : '' ) . 'src', $this->maybe_set_autoplay( $original_src, $tag ) );
			if ( ! empty( $info ) && 'video' === $info->type ) {
				$thumbnail_url = $this->maybe_translate_thumbnail_url( $info->thumbnail_url );
				$img           = $this->create_tag( 'img' );
				$new_tag       = $img;

				$local_thumbnail = $this->plugin->util->download_remote_file( $thumbnail_url, null, false );
				$this->maybe_generate_thumbnails( $local_thumbnail );
				$local_thumbnail = apply_filters( 'rocket_footer_js_lazyload_video_thumbnail', $local_thumbnail );
				$local_thumbnail = get_rocket_cdn_url( $local_thumbnail, [ 'css', 'js', 'css_and_js' ] );

				if ( ! empty( $this->srcset_attr ) ) {
					$img->setAttribute( 'srcset', $this->srcset_attr );
					$img->setAttribute( 'sizes', $this->sizes_attr );
				} else {
					$img->setAttribute( 'src', $local_thumbnail );
				}

				if ( ! $no_lazyload_thumbnail ) {
					$img->setAttribute( 'width', $info->thumbnail_width );
				}

				$type = $this->get_video_type( $src );

				$img->setAttribute( 'data-lazy-video-embed-type', $type );

				$video_id = $this->get_video_id( $src );

				if ( ! empty( $video_id ) ) {
					$img->setAttribute( 'class', "video-id-{$video_id}" );
				}

				$img->setAttribute( 'data-lazy-video-embed', "lazyload-video-{$this->instance}" );
				if ( $no_lazyload_thumbnail ) {
					$img->removeClass( 'lazyload' );
					$container = $this->create_tag( 'div' );
					$container->setAttribute( 'data-lazy-video-embed-container', $type );
					$container->appendChild( $img );
					$play = $this->create_tag( 'div' );
					$play->addClass( 'play' );
					$container->appendChild( $play );
					$new_tag = $container;
				}

				$linked = preg_grep( '/video-size-linked-to-[\w\__]+/', $classes );

				if ( ! empty( $linked ) ) {
					$linked_id = str_replace( 'video-size-linked-to-', '', end( $linked ) );
					$img->setAttribute( 'data-size-linked-to', $linked_id );
					unset( $classes[ array_search( $classes, end( $linked ) ) ] );
				}

				if ( isset( $info->width ) ) {
					$img->setAttribute( 'data-lazy-video-width', $info->width );
				}
				if ( isset( $info->height ) ) {
					$img->setAttribute( 'data-lazy-video-height', $info->height );
				}

				$tag->parentNode->insertBefore( $new_tag, $tag );
				$this->lazyload_script( $this->get_tag_content( $tag ), "lazyload-video-{$this->instance}", $tag );
				$tags->flag_removed();
				$this->instance ++;
			}

			$tag->setAttribute( 'class', implode( ' ', $classes ) );
		}
		$tags = $this->get_tag_collection( 'source' );
		foreach ( $tags as $tag ) {
			if ( $this->is_no_lazyload( $tag ) ) {
				continue;
			}
			$src = $tag->getAttribute( 'src' );
			if ( empty( $src ) ) {
				continue;
			}

			$tag->parentNode->lazyLoad();
			$tag->setAttribute( 'data-src', $src );
			$tag->removeAttribute( 'src' );
		}
	}

	/**
	 * @param string $url
	 */
	private function maybe_translate_url( $url ) {
		$url = set_url_scheme( $url );
		$url = parse_url( $url );
		if ( isset( $url['host'] ) && ( 'youtube.com' === $url['host'] || 'www.youtube.com' === $url['host'] ) ) {
			if ( false !== strpos( $url['path'], 'embed' ) ) {
				$video_id = pathinfo( $url['path'], PATHINFO_FILENAME );

				$url['path']  = '/watch';
				$url['query'] = http_build_query( [ 'v' => $video_id ] );
			}
		}
		$url = http_build_url( $url );

		return $url;
	}

	/**
	 * @param                              $url
	 * @param \Rocket\Footer\JS\DOMElement $tag
	 *
	 * @return mixed|string
	 */
	private function maybe_set_autoplay( $url, DOMElement $tag ) {
		$url = set_url_scheme( $url, 'https' );
		$url = parse_url( $url );
		if ( isset( $url['host'] ) && in_array( $url['host'], [
				'youtube.com',
				'www.youtube.com',
				'player.vimeo.com',
			] ) ) {
			$query = [];
			parse_str( $url['query'], $query );

			$url['query'] = http_build_query( array_merge( $query, [ 'autoplay' => '1' ] ) );
			$allow        = explode( ';', $tag->getAttribute( 'allow' ) );
			$allow        = array_map( 'trim', $allow );
			$allow        = array_filter( $allow );
			$allow[]      = 'autoplay';
			$allow        = array_unique( $allow );
			$tag->setAttribute( 'allow', implode( ';', $allow ) );
		}
		$url = http_build_url( $url );

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return array|mixed|string
	 */
	private function maybe_translate_thumbnail_url( $url ) {
		$url  = set_url_scheme( $url );
		$url  = parse_url( $url );
		$urls = [];
		if ( 'i.ytimg.com' === $url['host'] ) {
			$size_url = $url;
			$video_id = basename( pathinfo( $url['path'], PATHINFO_DIRNAME ) );
			foreach ( [ 'maxresdefault', 'hqdefault', 'sddefault', 'mqdefault' ] as $size ) {
				$size_url['path'] = "/vi/{$video_id}/{$size}.jpg";
				$urls[]           = http_build_url( $size_url );
			}
		}
		if ( ! empty( $urls ) ) {
			return $urls;
		}
		$url = http_build_url( $url );

		return $url;
	}

	/**
	 * @param $thumbnail_url
	 */
	private function maybe_generate_thumbnails( $thumbnail_url ) {
		$path = trailingslashit( $this->plugin->get_cache_path() );
		$info = pathinfo( parse_url( $thumbnail_url, PHP_URL_PATH ) );

		do_action( 'rocket_footer_js_lazyload_video_before_maybe_generate_thumbnails' );

		$editor = wp_get_image_editor( $path . $info['basename'] );

		if ( is_wp_error( $editor ) ) {
			return;
		}


		$file = trailingslashit( $path ) . $info['basename'];

		$file_info = wp_check_filetype( $file );
		$filetype  = null;
		if ( $file_info ) {
			$filetype = $file_info['type'];
		}

		list( $width, $height ) = getimagesize( $file );

		add_filter( 'wp_calculate_image_srcset', [ $this, 'save_srcsets' ] );
		add_filter( 'upload_dir', [ $this, 'modify_upload_dir' ] );

		$image_sizes = [];

		foreach ( get_intermediate_image_sizes() as $image_size ) {
			$image_size    = image_constrain_size_for_editor( $width, $height, $image_size );
			$image_sizes[] = [
				'width'  => $image_size[0],
				'height' => $image_size[1],
				'file'   => $info['filename'] . "-{$image_size[0]}x{$image_size[1]}" . '.' . $info['extension'],
			];
		}

		$image_sizes['thumbnail'] = [
			'width'     => $width,
			'height'    => $height,
			'file'      => $info['basename'],
			'mime-type' => $file_info,
		];

		$webp_module = $this->plugin->integration_manager->get_module( 'WebPExpress' );
		if ( $webp_module ) {
			$webp_module->disable_srcset_meta_filter();
		}

		do_action( 'rocket_footer_js_lazyload_video_before_calculate_srcset' );

		$this->srcset_attr = wp_calculate_image_srcset( [ $width, $height ], $file, [
			'sizes'  => $image_sizes,
			'file'   => $info['basename'],
			'width'  => $width,
			'height' => $height,
		] );
		$this->sizes_attr  = wp_calculate_image_sizes( [
			$width,
			$height,
		], $info['basename'], [ 'sizes' => $image_sizes ] );

		if ( $webp_module ) {
			$webp_module->enable_srcset_meta_filter();
		}

		$missing_image_sizes = array_filter( $image_sizes, function ( $size ) use ( $path ) {
			return false === $this->plugin->wp_filesystem->is_file( $path . $size['file'] );
		} );

		$image_sizes['thumbnail'] = [
			'width'     => $width,
			'height'    => $height,
			'file'      => $info['basename'],
			'mime-type' => $file_info,
		];

		$editor->multi_resize( $missing_image_sizes );

		$file              = apply_filters( 'rocket_footer_js_lazyload_video_thumbnail', site_url( str_replace( ABSPATH, '/', $file ) ) );
		$file              = str_replace( site_url( '/' ), ABSPATH, $file );
		$this->srcset_attr = wp_calculate_image_srcset( [
			$width,
			$height,
		], $file, [
			'sizes'  => $image_sizes,
			'file'   => $info['basename'],
			'width'  => $width,
			'height' => $height,
		] );
		$this->sizes_attr  = wp_calculate_image_sizes( [
			$width,
			$height,
		], $info['basename'], [ 'sizes' => $image_sizes ] );

		remove_filter( 'upload_dir', [ $this, 'modify_upload_dir' ] );

		do_action( 'rocket_footer_js_lazyload_video_after_calculate_srcset' );
	}

	/**
	 * @param $url
	 *
	 * @return string|null
	 */
	private function get_video_type( $url ) {
		$url  = parse_url( $url );
		$type = null;
		if ( 'youtube.com' === $url['host'] || 'www.youtube.com' === $url['host'] ) {
			$type = 'youtube';
		}
		if ( null !== $type ) {
			return $type;
		}

		return 'generic';
	}

	/**
	 * @param $url
	 *
	 * @return bool|mixed
	 */
	private function get_video_id( $url ) {
		$url = parse_url( $url );
		if ( 'youtube.com' === $url['host'] || 'www.youtube.com' === $url['host'] ) {
			if ( false !== strpos( $url['path'], 'embed' ) ) {
				return pathinfo( $url['path'], PATHINFO_FILENAME );
			}
			$query = [];
			parse_str( $url['query'], $query );

			return $query['v'];
		}

		return false;
	}

	/**
	 * @param $content
	 * @param $src
	 *
	 * @return bool
	 */
	protected function is_match( $content, $src ) {
		return false;
	}
}

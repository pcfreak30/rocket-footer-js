<?php


namespace Rocket\Footer\JS\Integration;


use WebPConvert\Converters\ConverterHelper;
use WebPConvert\Converters\Exceptions\ConversionDeclinedException;
use WebPConvert\Converters\Exceptions\ConverterFailedException;
use WebPExpress\AlterHtmlImageUrls;
use WebPExpress\Option;

/**
 * Class WebPExpress
 *
 * @package Rocket\Async\CSS\Integration
 */
class WebPExpress extends IntegrationAbstract {

	/**
	 * @var bool
	 */
	private $conditional = false;

	/**
	 * @var AlterHtmlImageUrls
	 */
	private $image_replace;
	/**
	 * @var bool
	 */
	private $webp_available = false;

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\WebPExpress\Config' ) && Option::getOption( 'webp-express-alter-html', false ) ) {
			$this->webp_available = true;
			$options              = json_decode( Option::getOption( 'webp-express-alter-html-options', null ), true );
			if ( 'url' === Option::getOption( 'webp-express-alter-html-replacement' ) && $options['only-for-webp-enabled-browsers'] ) {
				$this->conditional = true;
			}

			$autoload = WEBPEXPRESS_PLUGIN_DIR . '/vendor/autoload.php';
			if ( ! $this->plugin->wp_filesystem->is_file( $autoload ) ) {
				return;
			}

			require_once $autoload;

			if ( ! class_exists( '\WebPExpress\AlterHtmlImageUrls' ) ) {
				return;
			}

			$this->image_replace = new AlterHtmlImageUrls;

			add_filter( 'rocket_footer_js_lazyload_video_thumbnail', [ $this, 'maybe_process' ] );
			add_filter( 'image_get_intermediate_size', [ $this, 'filter_image_get_intermediate_size' ], 999999, 1 );
			add_filter( 'wp_calculate_image_srcset', [ $this, 'filter_wp_calculate_image_srcset' ], 999999, 1 );
			$this->enable_srcset_meta_filter();
			add_filter( 'mime_types', [ $this, 'add_webp_mime' ] );


			if ( false !== strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) ) {
				add_filter( 'rocket_footer_js_get_cache_id', [ $this, 'modify_cache_key' ] );
			}
		}
	}

	public function enable_srcset_meta_filter() {
		if ( ! $this->webp_available ) {
			return;
		}
		add_filter( 'wp_calculate_image_srcset_meta', [
			$this,
			'filter_wp_calculate_image_srcset_meta',
		], 999999, 1 );
	}

	public function filter_image_get_intermediate_size( $image ) {
		$image['url'] = $this->process_url( $image['url'] );

		return $image;
	}

	private function process_url( $url ) {

		if ( preg_match( '/\.webp$/', $url ) ) {
			return $url;
		}

		$domain      = $this->plugin->domain;
		$cdn_domains = $this->plugin->cdn_domains;

		$url_parts = parse_url( $url );
		$cdn       = false;

		if ( in_array( $url_parts['host'], $cdn_domains ) ) {
			$url_parts['host'] = $domain;
			$url               = http_build_url( $url_parts );
			$cdn               = true;
		}

		$new_url = $this->image_replace->replaceUrl( $url );
		if ( ! empty( $new_url ) ) {
			$url = $new_url;
			if ( $cdn ) {
				$url = get_rocket_cdn_url( $url, [ 'images' ] );
			}
		}

		return $url;
	}

	public function disable_srcset_meta_filter() {
		if ( ! $this->webp_available ) {
			return;
		}
		remove_filter( 'wp_calculate_image_srcset_meta', [
			$this,
			'filter_wp_calculate_image_srcset_meta',
		], 999999 );
	}

	public function filter_wp_calculate_image_srcset( $sources ) {

		foreach ( $sources as $key => $source ) {
			$sources[ $key ]['url'] = $this->process_url( $source['url'] );
		}

		return $sources;
	}

	public function filter_wp_calculate_image_srcset_meta( $image_meta ) {
		$upload_dir = wp_get_upload_dir();
		$baseurl    = trailingslashit( $upload_dir['baseurl'] );
		$dirname    = _wp_get_attachment_relative_path( $image_meta['file'] );
		if ( $dirname ) {
			$dirname = trailingslashit( $dirname );
		}
		$image_meta['file'] = str_replace( $dirname, '', $image_meta['file'] );
		$image_baseurl      = $baseurl . $dirname;
		$url                = $this->process_url( $image_baseurl . $image_meta['file'] );
		$dir                = $dirname;
		$image_meta['file'] = str_replace( $baseurl, '', $url );


		foreach ( $image_meta['sizes'] as $key => $image_size ) {
			$dirname = _wp_get_attachment_relative_path( $image_size['file'] );

			if ( $dirname ) {
				$dirname = trailingslashit( $dirname );
			}

			if ( empty( $dirname ) ) {
				$dirname = $dir;
			}

			$image_baseurl = $baseurl . $dirname;
			$url           = $image_baseurl . $image_meta['file'];
			$new_url       = $this->process_url( $url );
			if ( $new_url === $url ) {
				continue;
			}
			$image_meta['sizes'][ $key ]['mime-type'] = 'image/webp';
			$image_meta['sizes'][ $key ]['file']      = str_replace( $image_baseurl, '', $new_url );
		}

		return $image_meta;
	}

	/**
	 * @param $key
	 *
	 * @return array
	 */
	public function modify_cache_key( $key ) {
		if ( 2 === count( $key ) ) {
			$key[] = 'webp';
		} else {
			array_splice( $key, count( $key ) - 2, 0, [ 'webp' ] );
		}

		return $key;
	}

	/**
	 * @param $url
	 *
	 * @return string|string[]|null
	 * @throws \Exception
	 */
	public function maybe_process( $url ) {

		if ( ( $this->conditional && false !== strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) ) || ! $this->conditional ) {
			$new_url = $this->image_replace->replaceUrl( $url );
			if ( $new_url ) {
				$url_parts = parse_url( $url );
				$file      = untrailingslashit( ABSPATH ) . $url_parts['path'];
				$ext       = pathinfo( $url_parts['path'], PATHINFO_EXTENSION );
				$webp_file = preg_replace( "/\.{$ext}$/", '.webp', $file );
				if ( ! $this->plugin->wp_filesystem->is_file( $webp_file ) ) {
					if ( ! class_exists( '\WebPConvert\Converters\ConverterHelper' ) ) {
						require_once WEBPEXPRESS_PLUGIN_DIR . '/vendor/rosell-dk/webp-convert/build/webp-convert.inc';
					}
					try {
						ConverterHelper::runConverterStack( $file, $webp_file );
					} catch ( ConversionDeclinedException $e ) {
						return $url;
					} catch ( ConverterFailedException $e ) {
						return $url;
					}
				}
				$url = preg_replace( "/\.{$ext}$/", '.webp', $url );
			}

		}

		return $url;
	}

	/**
	 * @return bool
	 */
	public function is_webp_available() {
		return $this->webp_available;
	}

	public function add_webp_mime( $mimes ) {
		$mimes['webp'] = 'image/webp';

		return $mimes;
	}
}

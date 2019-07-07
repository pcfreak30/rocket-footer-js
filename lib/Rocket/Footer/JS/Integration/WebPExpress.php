<?php


namespace Rocket\Footer\JS\Integration;


use WebPConvert\Convert\ConverterFactory;
use WebPConvert\Exceptions\WebPConvertException;
use WebPExpress\AlterHtmlImageUrls;
use WebPExpress\Config;
use WebPExpress\ConvertersHelper;
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

			if ( ! defined( 'WEBPEXPRESS_PLUGIN_DIR' ) ) {
				return;
			}

			if ( ! $this->plugin->wp_filesystem->is_file( $autoload ) ) {
				return;
			}

			require_once $autoload;

			if ( ! class_exists( '\WebPExpress\AlterHtmlImageUrls' ) ) {
				return;
			}

			$this->image_replace = new AlterHtmlImageUrls;

			add_filter( 'rocket_footer_js_lazyload_video_thumbnail', [ $this, 'maybe_process' ] );
			add_filter( 'rocket_footer_js_webp_process_url', [ $this, 'maybe_process' ] );
			add_action( 'rocket_footer_js_webp_clear_minify_file_cache', [ $this, 'clear_minify_file_cache' ] );
			add_filter( 'image_get_intermediate_size', [ $this, 'filter_image_get_intermediate_size' ], 999999, 1 );
			add_filter( 'wp_calculate_image_srcset', [ $this, 'filter_wp_calculate_image_srcset' ], 999999, 1 );
			add_action( 'rocket_footer_js_lazyload_video_before_maybe_generate_thumbnails', [
				$this,
				'disable_intermediate_size',
			] );
			if ( ! is_admin() ) {
				$this->enable_srcset_meta_filter();
				add_filter( 'wp_get_attachment_metadata', [
					$this,
					'filter_wp_calculate_image_srcset_meta',
				], 999999, 1 );
				add_action( 'shutdown', [ $this, 'shutdown_hook_uploads' ], 0 );
			}
			add_filter( 'mime_types', [ $this, 'add_webp_mime' ] );


			if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) ) {
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

	public function clear_minify_file_cache( $url ) {
		$key = [ md5( $url ) ];
		$key = $this->modify_cache_key( $key );
		$this->plugin->cache_manager->get_store()->delete_cache_branch( $key );
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

	public function disable_intermediate_size() {
		remove_filter( 'image_make_intermediate_size', array(
			'\WebPExpress\HandleUploadHooks',
			'handleMakeIntermediateSize',
		) );
	}

	public function shutdown_hook_uploads() {
		add_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );
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
		if ( ! isset( $image_meta['file'] ) ) {
			return $image_meta;
		}
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

		if ( isset( $image_meta['sizes'] ) ) {
			foreach ( $image_meta['sizes'] as $key => $image_size ) {
				$dirname = _wp_get_attachment_relative_path( $image_size['file'] );

				if ( $dirname ) {
					$dirname = trailingslashit( $dirname );
				}

				if ( empty( $dirname ) ) {
					$dirname = $dir;
				}

				$image_baseurl = $baseurl . $dirname;
				$url           = $image_baseurl . $image_size['file'];
				$new_url       = $this->process_url( $url );
				if ( $new_url === $url ) {
					continue;
				}
				$image_meta['sizes'][ $key ]['mime-type'] = 'image/webp';
				$image_meta['sizes'][ $key ]['file']      = str_replace( $image_baseurl, '', $new_url );
			}
		}

		return $image_meta;
	}

	/**
	 * @param $url
	 *
	 * @return string|string[]|null
	 * @throws \Exception
	 */
	public function maybe_process( $url ) {

		if ( ( $this->conditional && false !== strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) ) || ! $this->conditional ) {
			add_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );
			$new_url = $this->image_replace->replaceUrl( $url );
			if ( $new_url ) {
				$url_parts = parse_url( $url );
				$file      = untrailingslashit( ABSPATH ) . $url_parts['path'];
				$ext       = pathinfo( $url_parts['path'], PATHINFO_EXTENSION );
				$webp_file = preg_replace( "/\.{$ext}$/", '.webp', $file );
				if ( ! $this->plugin->wp_filesystem->is_file( $webp_file ) ) {
					$class_found = false;
					if ( ! class_exists( '\WebPExpress\ConvertersHelper' ) ) {
						$autoload_file = WEBPEXPRESS_PLUGIN_DIR . '/vendor/autoload.php';

						if ( $this->plugin->wp_filesystem->is_file( $autoload_file ) ) {
							$class_found = true;
							require_once $autoload_file;
						}
					}
					if ( ! class_exists( '\WebPExpress\ConvertersHelper' ) ) {
						$convert_file = WEBPEXPRESS_PLUGIN_DIR . '/vendor/rosell-dk/webp-convert/src-build/webp-convert.inc';
						if ( $this->plugin->wp_filesystem->is_file( $convert_file ) ) {
							$class_found = true;
							require_once $convert_file;
						}
					}

					if ( class_exists( '\WebPExpress\ConvertersHelper' ) ) {
						$class_found = true;
					}

					if ( ! $class_found ) {
						error_log( sprintf( '%s: WebPExpress classes not found!', strtoupper( $this->plugin->safe_slug ) ) );
						remove_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );

						return $url;
					}

					try {
						/** @var \WebPConvert\Convert\Converters\AbstractConverter $converter */
						$converters = ConvertersHelper::getWorkingAndActiveConverters( Config::loadConfigAndFix( false ) );

						if ( ! is_array( $converters ) ) {
							remove_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );
							error_log( sprintf( '%s: WebPExpress has no converters setup', strtoupper( $this->plugin->safe_slug ) ) );

							return $url;
						}

						$converters = array_filter( $converters, function ( $item ) {
							if ( isset( $item['deactivated'] ) && $item['deactivated'] ) {
								return false;
							}
							if ( isset( $item['working'] ) && ! $item['working'] ) {
								return false;
							}

							return 'gd' !== $item['converter'];
						} );

						if ( 0 === count( $converters ) ) {
							remove_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );
							error_log( sprintf( '%s: WebPExpress has no supported converters setup', strtoupper( $this->plugin->safe_slug ) ) );

							return $url;
						}
						$converter = $converters[0];

						$converter = ConverterFactory::makeConverter( $converter['converter'], $file, $webp_file, $converter['options'] );
						$converter->doConvert();
					} catch ( WebPConvertException $e ) {
						error_log( sprintf( '%s: WebPExpress conversion attempt failed: %s', strtoupper( $this->plugin->safe_slug ), $e->getMessage() ) );
						remove_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );

						return $url;
					}
				}
				$url = preg_replace( "/\.{$ext}$/", '.webp', $url );
			}
			remove_filter( 'upload_dir', [ $this, 'override_upload_dir' ] );
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

	public function override_upload_dir( $upload ) {
		if ( false === strpos( $upload['subdir'], $this->plugin->cache_path ) ) {
			foreach ( [ 'path', 'url', 'basedir', 'baseurl' ] as $key ) {
				$upload[ $key ] = str_replace( $upload['subdir'], '', $upload[ $key ] );
				$upload[ $key ] = trailingslashit( dirname( $upload[ $key ] ) );
			}
		}

		return $upload;
	}
}

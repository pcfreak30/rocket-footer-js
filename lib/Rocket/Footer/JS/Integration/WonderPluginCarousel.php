<?php


namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS\TagHelperTrait;

class WonderPluginCarousel extends IntegrationAbstract {
	use TagHelperTrait;

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\WonderPlugin_Carousel_Plugin' ) ) {
			add_action( 'rocket_footer_js_do_rewrites', [ $this, 'process' ], 10, 2 );
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		}
	}

	public function process() {
		$document               = $this->plugin->document;
		$this->content_document = $document;
		$this->tags             = $this->get_script_collection();
		$run                    = false;
		while ( $this->tags->valid() ) {
			$tag = $this->tags->current();
			$src = $tag->getAttribute( 'src' );
			if ( empty( $src ) ) {
				$this->tags->next();
				continue;
			}
			$src = rocket_add_url_protocol( $src );
			if ( false !== strpos( $src, '?' ) ) {
				$src = substr( $src, 0, strpos( $src, strrchr( $src, '?' ) ) );
			}
			$src_host = parse_url( $src, PHP_URL_HOST );
			if ( $src_host != $this->plugin->domain && ! in_array( $src_host, $this->plugin->cdn_domains ) ) {
				$this->tags->next();
				continue;
			}
			if ( get_rocket_cdn_url( WP_PLUGIN_URL . '/wonderplugin-carousel/engine/wonderplugincarousel.js', [
					'all',
					'css',
					'js',
					'css_and_js',
				] ) === $src ) {
				$run = true;
				break;
			}
			$this->tags->next();
		}

		if ( $run ) {
			$xpath   = new \DOMXPath( $this->content_document );
			$vimeo   = false;
			$youtube = false;
			foreach ( $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " wonderplugincarousel ")]' ) as $tag ) {
				$data_vimeo   = $tag->getAttribute( 'data-initvimeo' );
				$data_youtube = $tag->getAttribute( 'data-inityoutube' );

				if ( $data_vimeo !== 'false' ) {
					$vimeo = true;
					$tag->setAttribute( 'data-initvimeo', 'false' );
				}
				if ( $data_youtube !== 'false' ) {
					$youtube = true;
					$tag->setAttribute( 'data-inityoutube', 'false' );
				}
				$tag->setAttribute( 'data-jsfolder', get_rocket_cdn_url( $tag->getAttribute( 'data-jsfolder' ) ) );
			}

			$doc = rocket_footer_js_container()->create( '\\Rocket\\Footer\\JS\\DOMDocument' );
			if ( $youtube ) {
				$external_tag = $doc->createElement( 'script' );
				$external_tag->setAttribute( 'src', 'https://www.youtube.com/iframe_api' );
				$external_tag->setAttribute( 'type', 'text/javascript' );
				$doc->appendChild( $external_tag );
			}
			if ( $vimeo ) {
				$this->inject_tag( $this->create_script( null, get_rocket_cdn_url( WONDERPLUGIN_CAROUSEL_URL . 'engine/froogaloop2.min.js' ) ) );
			}
			if ( $youtube || $vimeo ) {
				foreach ( $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " wondercarousellightbox_options ")]' ) as $tag ) {
					$tag->setAttribute( 'data-jsfolder', get_rocket_cdn_url( $tag->getAttribute( 'data-jsfolder' ) ) );
					if ( $vimeo ) {
						$tag->setAttribute( 'data-initvimeo', 'false' );
					}
					if ( $youtube ) {
						$tag->setAttribute( 'data-inityoutube', 'false' );
					}
				}
				do_action( 'rocket_footer_js_do_rewrites', $this->plugin->script_document, $doc );
			}
		}

	}

	public function scripts() {
		if ( $this->plugin->lazyload_manager->is_enabled() ) {
			wp_add_inline_script( 'jquery-core', '(function($){$(".wonderplugincarousel img").one("lazyload",function(){$(this).closest(".wonderplugincarousel").data("object").resizeCarousel()});$(document).on("amazingcarousel.switch", ".wonderplugincarousel", function(){$(window).lazyLoadXT()});})(jQuery);' );
		}
	}
}
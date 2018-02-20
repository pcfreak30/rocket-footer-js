<?php


namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS\Rewrite\FacebookPixel;

/**
 * Class PixelYourSite
 *
 * @package Rocket\Footer\JS\Integration
 */
class PixelYourSite extends IntegrationAbstract {
	/**
	 *
	 */
	public function init() {
		if ( function_exists( 'pys_free_init' ) || function_exists( 'pys_fb_pixel_pro_activation' ) ) {
			add_filter( 'rocket_footer_js_process_local_script', [ $this, 'process' ], 10, 2 );
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 11 );
		}
	}

	/**
	 * @param $script
	 * @param $url
	 *
	 * @return string
	 */
	public function process( $script, $url ) {
		if ( set_url_scheme( WP_PLUGIN_URL . '/pixelyoursite/js/public.js' ) === $url ||
		     get_rocket_cdn_url( set_url_scheme( WP_PLUGIN_URL . '/pixelyoursite/js/public.js' ), [
			     'all',
			     'css',
			     'js',
			     'css_and_js',
		     ] ) === $url ||
		     set_url_scheme( WP_PLUGIN_URL . '/pixelyoursite-pro/js/public.js' ) === $url ||
		     get_rocket_cdn_url( set_url_scheme( WP_PLUGIN_URL . '/pixelyoursite-pro/js/public.js' ), [
			     'all',
			     'css',
			     'js',
			     'css_and_js',
		     ] ) === $url ) {
			/** @var FacebookPixel $fb_pixel */
			$fb_pixel = rocket_footer_js()->get_rewrite_manager()->get_module( 'FacebookPixel' );
			if ( ! empty( $fb_pixel ) ) {
				$regex                  = $fb_pixel->get_regex();
				$this->tags             = rocket_footer_js()->get_dom_collection();
				$this->content_document = rocket_footer_js()->get_script_document();
				if ( preg_match( $regex, $script, $matches ) ) {
					$script = '(function(a){a.fbq||(n=a.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)},a._fbq||(a._fbq=n));n.push=n;n.disableConfigLoading=!0;n.loaded=!0;n.version="2.0";n.queue=[]})(window);' . str_replace( $matches[0], '', $script );
					$this->tags->add( $this->create_script( null, $matches[1] ) );
					$this->tags->add( $this->create_script( '(function(){var pixel;if(typeof pys_events!=="undefined")pixel=pys_events.name;if(typeof pys_fb_pixel_regular_events!=="undefined")for(var i=0;i<pys_fb_pixel_regular_events.length;i++)if("init"===pys_fb_pixel_regular_events[i].type){pixel=pys_fb_pixel_regular_events[i].name;break}if(!pixel)return;fbq.registerPlugin("config"+pixel,{__fbEventsPlugin:1,plugin:function(f,i){i.configLoaded(pixel)}})})();' ) );
					$this->tags->add( $this->create_script( null, str_replace( 'fbevents.js', 'fbevents.plugins.identity.js', $matches[1] ) ) );
				}
			}

		}

		return $script;
	}

	public function scripts() {
		if ( wp_script_is( 'pys-yt-track' ) ) {
			wp_enqueue_script( 'youtube-embed-api', 'https://www.youtube.com/iframe_api', [], null );
			/* @var \_WP_Dependency $script */
			$script = wp_scripts()->registered['pys-yt-track'];
			wp_dequeue_script( 'pys-yt-track' );
			wp_deregister_script( 'pys-yt-track' );
			wp_enqueue_script( 'pys-yt-track', $script->src, array_merge( $script->deps, [ 'youtube-embed-api' ] ) );
		}
	}
}
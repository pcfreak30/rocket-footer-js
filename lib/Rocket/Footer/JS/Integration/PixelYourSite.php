<?php


namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\Rewrite\FacebookPixel;
use Rocket\Footer\JS\TagHelperTrait;

/**
 * Class PixelYourSite
 *
 * @package Rocket\Footer\JS\Integration
 */
class PixelYourSite implements IntegrationInterface {
	use TagHelperTrait;
	/**
	 * @var DOMDocument
	 */
	protected $content_document;
	/**
	 * @var DOMCollection
	 */
	protected $tags;

	/**
	 *
	 */
	public function init() {
		if ( function_exists( 'pys_free_init' ) ) {
			add_filter( 'rocket_footer_js_process_local_script', [ $this, 'process' ] );
		}
	}

	/**
	 * @param $script
	 * @param $url
	 *
	 * @return string
	 */
	public function process( $script, $url ) {
		if ( set_url_scheme( WP_PLUGIN_URL . '/pixelyoursite/js/public.js' ) === $url ) {
			/** @var FacebookPixel $fb_pixel */
			$fb_pixel               = rocket_footer_js()->get_rewrite_manager()->get_module( 'FacebookPixel' );
			$regex                  = $fb_pixel->get_regex();
			$this->tags             = rocket_footer_js()->get_dom_collection();
			$this->content_document = rocket_footer_js()->get_script_document();
			if ( preg_match( $regex, $script, $matches ) ) {
				$script = '(function(a){a.fbq||(n=a.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)},a._fbq||(a._fbq=n));n.push=n;n.disableConfigLoading=!0;n.loaded=!0;n.version="2.0";n.queue=[]})(window);' . str_replace( $matches[0], '', $script );
				$this->tags->add( $this->create_script( null, $matches[1] ) );
				$this->tags->add( $this->create_script( 'fbq.registerPlugin("config" + pys_events.name, {__fbEventsPlugin: 1,plugin: function(f, i){i.configLoaded(pys_events.name);}});' ) );
				$this->tags->add( $this->create_script( null, str_replace( 'fbevents.js', 'fbevents.plugins.identity.js', $matches[1] ) ) );
			}
		}

		return $script;
	}
}
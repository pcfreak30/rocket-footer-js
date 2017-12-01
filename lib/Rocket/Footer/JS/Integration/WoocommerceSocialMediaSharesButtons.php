<?php


namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS\DOMElement;

class WoocommerceSocialMediaSharesButtons extends IntegrationAbstract {

	public function init() {
		if ( function_exists( 'toastie_wc_smsb_social_init' ) ) {
			add_filter( 'rocket_footer_js_process_local_script', [ $this, 'process' ], 10, 2 );
		}
	}

	public function process( $script, $url ) {
		if ( set_url_scheme( WP_PLUGIN_URL . '/woocommerce-social-media-share-buttons/smsb_script.js' ) === $url ) {

			$script = str_replace( "\n", '', $script );
			if ( preg_match_all( '~\\(function.*(?:\)\)|}\)\(\));~U', $script, $matches ) ) {
				$doc = rocket_footer_js_container()->create( '\\Rocket\\Footer\\JS\\DOMDocument' );
				foreach ( $matches[0] as $match ) {
					/** @var DOMElement $tag */
					$tag = $doc->createElement( 'script' );
					$cm  = $doc->createTextNode( "\n//" );
					$ct  = $doc->createCDATASection( "\n" . $match . "\n//" );
					$tag->appendChild( $cm );
					$tag->appendChild( $ct );
					$doc->appendChild( $tag );
					$script = str_replace( $match, '', $script );
				}
				do_action( 'rocket_footer_js_do_rewrites', $this->plugin->script_document, $doc );
				do_action( 'rocket_footer_js_do_lazyload', $this->plugin->script_document, $doc );
			}
		}

		return $script;
	}
}
<?php


namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS;
use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\TagHelperTrait;

class Tawkto implements IntegrationInterface {
	use TagHelperTrait;
	/**
	 * @var DOMDocument
	 */
	protected $content_document;
	/**
	 * @var DOMCollection
	 */
	protected $tags;


	public function init() {
		if ( function_exists( 'toastie_wc_smsb_social_init' ) ) {
			add_filter( 'rocket_footer_js_process_remote_script', [ $this, 'process' ] );
		}
	}

	public function process( $script, $url ) {
		global $rocket_async_css_file;
		if ( 'embed.tawk.to' === parse_url( $url, PHP_URL_HOST ) ) {
			$this->content_document = rocket_footer_js()->get_script_document();
			$this->tags             = rocket_footer_js()->get_dom_collection();
			if ( preg_match( '~\w\.src\s*=\s*"(https://cdn\.jsdelivr\.net/emojione/[\d\.]+/lib/js/emojione\.min\.js)"\s*;~', $script, $matches ) ) {
				$script = str_replace( $matches[0], '', $script );
				$this->tags->add( $this->create_script( null, $matches[1] ) );
			}
			if ( ! empty( $rocket_async_css_file ) && class_exists( 'Rocket_Async_Css' ) && method_exists( 'Rocket_Async_Css', 'minify_remote_file' ) && preg_match( '~\w\.href\s*=\s*"(https://cdn\.jsdelivr\.net/emojione/[\d\.]+/assets/css/emojione\.min\.css)"\s*;~', $script, $matches ) ) {
				$script        = str_replace( $matches[0], '', $script );
				$style         = rocket_footer_js()->get_content( $rocket_async_css_file );
				$item_cache_id = md5( $matches[1] );
				$store         = rocket_footer_js()->get_cache_manager()->get_store();
				$store->set_prefix( Rocket_Async_Css::TRANSIENT_PREFIX );
				$file = $store->get_cache_fragment( $item_cache_id );
				if ( empty( $file ) ) {
					$file = rocket_footer_js()->remote_fetch( $matches[1] );
				}
				// Do nothing on error
				if ( ! empty( $file ) ) {
					$css_part = Rocket_Async_Css::get_instance()->minify_remote_file( $url, $file );
					$style    .= $css_part;
					$store->update_cache_fragment( $item_cache_id, $css_part );
					rocket_footer_js()->put_content( $rocket_async_css_file, $style );
				}
				$store->set_prefix( JS::TRANSIENT_PREFIX );
			}
		}

		return $script;
	}
}
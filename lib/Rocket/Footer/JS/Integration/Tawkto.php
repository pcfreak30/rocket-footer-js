<?php


namespace Rocket\Footer\JS\Integration;


class Tawkto extends IntegrationAbstract {

	public function init() {
		if ( function_exists( 'toastie_wc_smsb_social_init' ) ) {
			add_filter( 'rocket_footer_js_process_remote_script', [ $this, 'process' ], 10, 2 );
		}
	}

	public function process( $script, $url ) {
		if ( 'embed.tawk.to' === parse_url( $url, PHP_URL_HOST ) ) {
			$this->content_document = $this->plugin->script_document;
			$this->tags             = $this->plugin->dom_collection;
			if ( preg_match( '~\w\.src\s*=\s*"(https://cdn\.jsdelivr\.net/emojione/[\d\.]+/lib/js/emojione\.min\.js)"\s*;~', $script, $matches ) ) {
				$script = str_replace( $matches[0], '', $script );
				$this->tags->add( $this->create_script( null, $matches[1] ) );
			}
			if ( function_exists( 'rocket_async_css_instance' ) && preg_match( '~\w\.href\s*=\s*"(https://cdn\.jsdelivr\.net/emojione/[\d\.]+/assets/css/emojione\.min\.css)"\s*;~', $script, $matches ) ) {
				$script = str_replace( $matches[0], '', $script );
				$files  = rocket_async_css_instance()->get_files();
				if ( ! empty( $files['all'] ) ) {
					$style         = $this->plugin->get_content( $files['all'] );
					$item_cache_id = md5( $matches[1] );
					$store         = $this->plugin->cache_manager->get_store();
					$store->set_prefix( rocket_async_css_instance()->get_transient_prefix() );
					$file = $store->get_cache_fragment( $item_cache_id );
					if ( empty( $file ) ) {
						$file = $this->plugin->remote_fetch( $matches[1] );
					}
					// Do nothing on error
					if ( ! empty( $file ) ) {
						$css_part = rocket_async_css_instance()->minify_remote_file( $url, $file );
						$style    .= $css_part;
						$store->update_cache_fragment( $item_cache_id, $css_part );
						$this->plugin->put_content( $files['all'], $style );
					}
					$store->set_prefix( $this->plugin->get_transient_prefix() );
				}
			}
		}

		return $script;
	}
}
<?php


namespace Rocket\Footer\JS\Rewrite;

/**
 * Class HubSpot
 *
 * @package Rocket\Footer\JS\Rewrite
 */
class HubSpot extends RewriteAbstract {
	/**
	 * @var array
	 */
	private $scripts = [];

	/**
	 *
	 */
	public function init() {
		parent::init();
		add_action( 'rocket_footer_js_process_remote_script', [ $this, 'process_remote_script' ], 10, 2 );
	}

	/**
	 * @param $content
	 * @param $src
	 */
	public function process_remote_script( $content, $src ) {
		if ( 'js.hs-analytics.net' === parse_url( $src, PHP_URL_HOST ) ) {
			preg_match_all( '~_hsq\s*\.\s*push\s*\(\s*(\[.*\])\)\s*;~U', $content, $matches );
			foreach ( array_keys( $matches[1] ) as $index ) {
				$item = $matches[1][ $index ];
				$item = json_decode( str_replace( "'", '"', $item ), true );
				if ( ! $item ) {
					$item = [];
				}
				$matches[1][ $index ] = $item;
			}
			if ( ! empty( $matches ) ) {
				foreach ( $matches[1] as $index => $item ) {
					if ( 'embedHubSpotScript' === $item[0] ) {
						$url   = parse_url( $item[1] );
						$found = false;
						foreach ( $this->scripts as $script ) {
							if ( $url['host'] === $script['host'] && $url['path'] === $script['path'] ) {
								$found = true;
								break;
							}
						}
						if ( $found ) {
							$content = str_replace( $matches[0], '', $content );
						}
					}
				}
			}
		}

		return $content;
	}

	/**
	 * @param string $content
	 *
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( 'js.hsforms.net' === parse_url( $src, PHP_URL_HOST ) ) {
			$this->set_no_minify();

			return;
		}

		if ( 'js.hs-scripts.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$file = $this->plugin->remote_fetch( $src );
			if ( ! empty( $file ) ) {
				$file            = str_replace( "\n", '', $file );
				$matched         = false;
				$external_script = $this->create_script();
				$this->set_no_minify( $external_script );
				foreach (
					[
						'~\(\s*[\'"]CollectedForms-\d+[\'"]\s*,\s*[\'"]((?:https?:)?//js\.hscollectedforms\.net/collectedforms\.js)[\'"]\s*,\s*({.*})\s*\)\d*;~U',
						'~\(\s*[\'"]hs-analytics[\'"]\s*,\s*[\'"]((?:https?:)?//js\.hs-analytics\.net/analytics/\d+/\d+.js)[\'"]\s*,\s*({.*})\s*\)\d*;~U',
						'~\(\s*[\'"]messages-\d+[\'"]\s*,\s*[\'"]((?:https?:)?//api.usemessages.com/messages/v2/embed/\d+.js)[\'"]\s*,\s*({.*})\s*\)\d*;~U',

					] as $regex
				) {
					if ( preg_match( $regex, $file, $matches ) ) {
						$matched = true;
						$this->inject_tag( $this->create_script( null, $matches[1] ) );
						$this->scripts[] = parse_url( $matches[1] );
						$json            = json_decode( $matches[2], true );
						foreach ( $json as $key => $val ) {
							$external_script->setAttribute( $key, $val );
						}
					}
				}
				if ( $matched ) {
					$this->scripts[] = parse_url( $src );
					$this->inject_tag( $external_script );
					$this->tags->remove();
				}
			}
		}

	}
}

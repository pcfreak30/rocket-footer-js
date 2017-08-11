<?php


namespace Rocket\Footer\JS\Rewrite;


class HubSpot extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( 'js.hs-scripts.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$file = rocket_footer_js()->remote_fetch( $src );
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
						$json = json_decode( $matches[2], true );
						foreach ( $json as $key => $val ) {
							$external_script->setAttribute( $key, $val );
						}
					}
				}
				if ( $matched ) {
					$this->inject_tag( $external_script );
					$this->tags->remove();
				}
			}
		}

	}
}
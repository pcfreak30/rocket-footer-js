<?php


namespace Rocket\Footer\JS\Integration;


class CrazyEgg extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_filter( 'rocket_footer_js_process_remote_script', [ $this, 'process' ], 10, 2 );
	}

	public function process( $script, $url ) {
		if ( 'script.crazyegg.com' === parse_url( $url, PHP_URL_HOST ) ) {
			$script = base64_encode( $script );
			$script = "eval(atob('$script'));";
		}

		return $script;
	}
}
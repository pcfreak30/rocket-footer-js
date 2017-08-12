<?php


namespace Rocket\Footer\JS\Rewrite;


class PingdomPrum extends RewriteAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( false !== strpos( $content, 'rum-static.pingdom.net/prum.min.js' ) ) {
			$this->inject_tag( $this->create_script( "_prum = typeof _prum !== 'undefined' ? _prum : [];" ) );
			$this->inject_tag( $this->create_script( null, 'https://rum-static.pingdom.net/prum.min.js' ) );
			$this->tags->remove();
		}
	}
}
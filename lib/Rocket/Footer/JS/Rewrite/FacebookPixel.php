<?php


namespace Rocket\Footer\JS\Rewrite;


class FacebookPixel extends RewriteAbstract {

	protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
		$regex = '~!?function\s*\(\s*f\s*,\s*b\s*,\s*e\s*,\s*v\s*,\s*n\s*,\s*t\s*,\s*s\s*\)\s*{\s*if\s*\(\s*f\s*\.\s*fbq\s*\)\s*return\s*;\s*n\s*=\s*f\s*.\s*fbq\s*=\s*function.*\s*\(\s*window\s*,\s*document\s*,\s*\'script\'\s*,\s*\'((?:https?:)?//connect.facebook.net/[\w_]+/fbevents.js)\'\s*\)\s*;~s';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( preg_match( $this->regex, $content, $matches ) ) {
			preg_match_all( '~fbq\s*\(\s*(.*)\s*,\s*(.*)\s*\)\s*;~U', $content, $fbq_calls, PREG_SET_ORDER );
			foreach ( $fbq_calls as $index => $fbq_call ) {
				if ( ! empty( $fbq_call[1] ) && 'init' === trim( $fbq_call[1], "'" ) ) {
					$pixel_id = $fbq_call[2];
				}
				$fbq_calls[ $index ] = array( $fbq_call[0] );
			}
			if ( ! empty( $pixel_id ) ) {
				$fbq_calls = call_user_func_array( 'array_merge', $fbq_calls );
				$this->inject_tag( $this->create_script( '(function(a){a.fbq||(n=a.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)},a._fbq||(a._fbq=n));n.push=n;n.disableConfigLoading=!0;n.loaded=!0;n.version="2.0";n.queue=[]})(window);' . implode( "\n", $fbq_calls ) ) );
				$this->inject_tag( $this->create_script( null, $matches[1] ) );
				$this->inject_tag( $this->create_script( 'fbq.registerPlugin("config:"+' . $pixel_id . ', {__fbEventsPlugin: 1,plugin: function(f, i){i.configLoaded(' . $pixel_id . ');}});' ) );
				$this->inject_tag( $this->create_script( null, str_replace( 'fbevents.js', 'fbevents.plugins.identity.js', $matches[1] ) ) );

				$content = str_replace( $matches[0], '', $content );
				foreach ( $fbq_calls as $fbq_call ) {
					$content = str_replace( $fbq_call, '', $content );
				}
				$content = trim( $content );
				if ( ! empty( $content ) ) {
					$this->inject_tag( $this->create_script( $content ) );
				}

				$content = trim( str_replace( $matches[0], '', $content ) );
				$content = trim( str_replace( $fbq_calls, '', $content ) );
				$this->inject_tag( $this->create_script( $content ) );

				$this->tags->remove();
			}

		}
	}
}
<?php


namespace Rocket\Footer\JS\Integration;


class NExtend extends IntegrationAbstract {

	private $override_added = false;

	public function init() {
		if ( class_exists( 'N2Pluggable' ) ) {
			\N2Pluggable::addAction( 'systemglobal', [ $this, 'disable' ] );
			\N2Settings::init();
		}
		add_action( 'plugins_loaded', [ $this, 'check' ], 21 );
	}

	public function disable( $referenceKey, &$rows ) {
		/** @noinspection ReferenceMismatchInspection */
		foreach ( array_keys( $rows ) as $key ) {
			if ( in_array( $rows[ $key ]['referencekey'], [
				'async',
				'combine-js',
				'minify-js',
				'protocol-relative',
				'curl',
			] ) ) {
				$rows[ $key ]['value'] = 0;
			}
		}
	}

	public function check() {
		if ( class_exists( 'N2SmartsliderApplicationInfo' ) ) {
			\N2AssetsManager::getInstance();
			\N2AssetsManager::disableCacheAll();
			add_filter( 'do_shortcode_tag', [ $this, 'maybe_add_override_js' ], 10, 2 );
		}
	}

	public function maybe_add_override_js( $output, $tag ) {
		if ( in_array( $tag, [ 'smartslider3', 'fusion_smartslider3' ] ) && ! $this->override_added ) {
			\N2JS::addCode( '(function(){var original=N2Classes.SmartSliderAbstract.prototype.constructor;N2Classes.SmartSliderAbstract.prototype.constructor=function(){var that = this;var args=[].slice.call(arguments);jQuery(window).load(function(){original.apply(that,args)})}})();', 'smartslider-frontend-override' );
			$this->override_added = true;
		}

		return $output;
	}
}

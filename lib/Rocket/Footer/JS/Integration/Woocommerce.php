<?php


namespace Rocket\Footer\JS\Integration;


class Woocommerce extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\WooCommerce' ) ) {
			remove_filter( 'nocache_headers', [ 'WC_Cache_Helper', 'set_nocache_constants' ] );
		}
	}
}
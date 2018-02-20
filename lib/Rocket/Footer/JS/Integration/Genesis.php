<?php


namespace Rocket\Footer\JS\Integration;


class Genesis extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_check' ] );

	}

	public function theme_check() {
		if ( function_exists( 'genesis') && 0 < (int) get_rocket_option( 'cdn' ) ) {
			add_action( 'theme_mod_header_image', [ $this, 'cdnify_header_image' ] );
		}
	}

	public function cdnify_header_image($image) {
		$image = get_rocket_cdn_url( $image, [
			'all',
			'images',
		] );
		return $image;
	}
}
<?php


namespace Rocket\Footer\JS\Integration;


class WPEXThemeFramework extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_check' ], 0 );
	}

	public function theme_check() {
		if ( class_exists( '\WPEX_Theme_Setup' ) ) {
			$this->image_class_override();
			add_filter( 'option_theme_mods_' . get_option( 'stylesheet' ), [ $this, 'process_theme_mods' ] );
			add_filter( 'wpex_header_logo_img_url', 'get_rocket_cdn_url' );
		}
	}

	private function image_class_override() {
		require_once trailingslashit( dirname( $this->plugin->plugin_file ) ) . trailingslashit( 'lib' ) . trailingslashit( 'overrides' ) . trailingslashit( 'wpex-theme-framework' ) . 'image-resize.php';
	}

	public function process_theme_mods( $value ) {
		foreach (
			array(
				'favicon',
				'iphone_icon',
				'ipad_icon',
				'iphone_icon_retina',
				'ipad_icon_retina',
			) as $icon
		) {
			$value[ $icon ] = get_rocket_cdn_url( wp_get_attachment_image_src( $value[ $icon ], 'full' ), [
				'all',
				'images',
			] );
		}

		return $value;
	}
}
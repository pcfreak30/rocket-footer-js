<?php


namespace Rocket\Footer\JS\Integration;


class BridgeTheme extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_check' ] );

	}

	public function theme_check() {
		if ( class_exists( '\QodeFramework' ) && 'bridge' === wp_get_theme()->template ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 12 );
		}
	}

	public function scripts() {
		if ( wp_script_is( 'plugins' ) && wp_script_is( 'youtube-embed-api' ) ) {
			/* @var \_WP_Dependency $script */
			$script = wp_scripts()->registered['youtube-embed-api'];
			wp_dequeue_script( 'youtube-embed-api' );
			wp_deregister_script( 'youtube-embed-api' );
			wp_enqueue_script( 'youtube-embed-api', $script->src, array_merge( $script->deps, [ 'plugins' ] ) );
		}
	}
}
<?php


namespace Rocket\Footer\JS\Integration;


class QcodeThemeFramework extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_check' ] );

	}

	public function theme_check() {
		if ( class_exists( '\QodeFramework' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 1000 );
		}
	}

	public function scripts() {
		if ( wp_script_is( 'default' ) ) {
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				/* @var \_WP_Dependency $script */
				$script = wp_scripts()->registered['default'];
				wp_dequeue_script( 'default' );
				wp_deregister_script( 'default' );
				wp_enqueue_script( 'default', $script->src, array_merge( $script->deps, array_filter( [
					wp_script_is( 'default_dynamic' ) ? 'default_dynamic' : null,
					'jquery-lazyloadxt.bg',
				] ) ) );
			}
			wp_add_inline_script( 'default', '(function($){$(function(){window.qode_body = $("body")});})(jQuery);', 'before' );

		}
		if ( wp_script_is( 'default_dynamic' ) ) {
			$script = wp_scripts()->registered['default_dynamic'];
			wp_dequeue_script( 'default_dynamic' );
			wp_deregister_script( 'default_dynamic' );
			wp_enqueue_script( 'default_dynamic', $script->src, false, false );
		}
	}
}

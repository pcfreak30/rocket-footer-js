<?php


namespace Rocket\Footer\JS\Integration;


class RankMathSEO extends IntegrationAbstract {
	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\RankMath' ) ) {
			foreach ( [ 'facebook', 'twitter' ] as $network ) {
				add_action( "rank_math/opengraph/{$network}/add_images", [ $this, 'disable_webp' ], 10, 0 );
				add_action( "rank_math/opengraph/{$network}/add_additional_images", [ $this, 'enable_webp' ], 10, 0 );
			}
		}
	}

	public function disable_webp() {
		add_filter( 'rocket_async_css_webp_enabled', '__return_false' );
	}

	public function enable_webp() {
		remove_filter( 'rocket_async_css_webp_enabled', '__return_false' );
	}
}

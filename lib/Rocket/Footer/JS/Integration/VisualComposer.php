<?php


namespace Rocket\Footer\JS\Integration;


class VisualComposer extends IntegrationAbstract {

	public function init() {
		if ( class_exists( '\Vc_Manager' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, [ $this, 'lazyload' ], 10, 3 );
		}
	}

	public function lazyload( $classes, $tag, $attr ) {
		$classes_list = explode( ' ', $classes );
		$classes_list = array_map( 'trim', $classes_list );
		$classes_list = array_filter( $classes_list );

		$found = preg_grep( '/^vc_custom_[0-9]+/', $classes_list );

		if ( $found ) {
			$custom_class = end( $found );
			if ( preg_match( "/{$custom_class}\s*{\s*background(?:-image)?:\s*url\\(\\s*(['\"]?(.*?)['\"]?|[^\\)\\s]+)\\s*\\)/", $attr['css'] ) ) {
				$classes_list[] = 'lazy_background';
				$classes_list[] = 'lazyload';
				$classes        = implode( ' ', $classes_list );
			}
		}

		return $classes;
	}

}

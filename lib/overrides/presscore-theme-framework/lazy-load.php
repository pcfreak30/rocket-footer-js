<?php
if ( ! function_exists( 'presscore_lazy_loading_enabled' ) ) {
	/**
	 * @return bool
	 */
	function presscore_lazy_loading_enabled() {
		return false;
	}
}
if ( ! function_exists( 'presscore_masonry_lazy_loading' ) ) {
	/**
	 * @param string $output
	 * @param array  $args
	 *
	 * @return string
	 */
	function presscore_masonry_lazy_loading( $output = '', $args = array() ) {
		return $output;
	}
}
if ( ! function_exists( 'presscore_wc_image_lazy_loading' ) ) {
	/**
	 * @param $attr
	 * @param $attachment
	 * @param $size
	 *
	 * @return mixed
	 */
	function presscore_wc_image_lazy_loading( $attr, $attachment, $size ) {
		return $attr;
	}
}
if ( ! function_exists( 'presscore_add_preload_me_class_to_images' ) ) {
	/**
	 * @param array $args
	 *
	 * @return array
	 */
	function presscore_add_preload_me_class_to_images( $args = [] ) {
		return $args;
	}
}
if ( ! function_exists( 'dt_woocommerce_subcategory_thumbnail' ) ) :

	/**
	 * Display woocommerce_subcategory_thumbnail() wrapped with 'a' targ.
	 *
	 * @param  mixed  $category
	 * @param  string $class
	 */
	function dt_woocommerce_subcategory_thumbnail( $category, $class = '' ) {
		ob_start();
		woocommerce_subcategory_thumbnail( $category );
		$img = ob_get_contents();
		ob_end_clean();
		echo '<a href="' . get_term_link( $category->slug, 'product_cat' ) . '" class="' . esc_attr( $class ) . '">' . $img . '</a>';
	}

endif;
if ( ! function_exists( 'presscore_get_image_with_srcset' ) ) :

	function presscore_get_image_with_srcset( $regular, $retina, $default, $custom = '', $class = '' ) {
		$srcset = array();

		foreach ( array( $regular, $retina ) as $img ) {
			if ( $img ) {
				$srcset[] = "{$img[0]} {$img[1]}w";
			}
		}

		$output = '<img class="' . esc_attr( $class ) . '" src="' . esc_attr( $default[0] ) . '" srcset="' . esc_attr( implode( ', ', $srcset ) ) . '" ' . image_hwstring( $default[1], $default[2] ) . ' ' . $custom . ' />';

		return $output;
	}

endif;
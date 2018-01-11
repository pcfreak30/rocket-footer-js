<?php


namespace Rocket\Footer\JS\Integration;


class GoogleAnalytics extends IntegrationAbstract {

	public function init() {
		add_action( 'init', [ $this, 'init_action' ], 11 );
	}

	public function init_action() {
		if ( class_exists( 'Ga_Helper' ) && ! is_admin() ) {
			remove_action( 'wp_footer', 'Ga_Frontend::insert_ga_script' );
			if ( \Ga_Helper::can_add_ga_code() || \Ga_Helper::is_all_feature_disabled() ) {
				add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );
				add_action( 'wp_head', [ $this, 'inject_ga_function' ] );
			}
		}
	}

	public function enqueue_script() {
		$web_property_id = \Ga_Frontend::get_web_property_id();
		if ( \Ga_Helper::should_load_ga_javascript( $web_property_id ) ) {
			$javascript = \Ga_View_Core::load( 'ga_code', array(
				'data' => array(
					\Ga_Admin::GA_WEB_PROPERTY_ID_OPTION_NAME => $web_property_id,
				),
			), true );
			$javascript = strip_tags( $javascript );
			wp_add_inline_script( 'jquery-core', $javascript );
		}
	}

	public function inject_ga_function() {
		?>
		<script type="text/javascript" data-no-minify="1">
					(function (i, r) {
						i[ 'GoogleAnalyticsObject' ] = r;
						i[ r ] = i[ r ] || function () {
							(i[ r ].q = i[ r ].q || []).push(arguments)
						}, i[ r ].l = 1 * new Date();
					})(window, 'ga');
		</script>
		<?php
	}
}
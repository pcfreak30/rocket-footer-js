<?php


namespace Rocket\Footer\JS\Integration;


class VidBgPro extends IntegrationAbstract {

	public function init() {
		if ( function_exists( 'vidbgpro_init_footer' ) && rocket_footer_js()->get_lazyload_manager()->is_enabled() ) {
			remove_action( 'wp_footer', 'vidbgpro_init_footer' );
			add_action( 'wp_footer', [ $this, 'wp_footer' ] );
		}
	}

	public function wp_footer() {
		ob_start();
		vidbgpro_init_footer();
		$output = ob_get_clean();
		if ( ! empty( $output ) ) {
			?>
			<div id="vidbgpro-page">
				<!-- <?php echo $output; ?>-->

			</div>
			<?php
		}
	}
}
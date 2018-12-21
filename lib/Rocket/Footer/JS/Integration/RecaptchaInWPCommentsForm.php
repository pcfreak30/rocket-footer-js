<?php


namespace Rocket\Footer\JS\Integration;


class RecaptchaInWPCommentsForm extends IntegrationAbstract {

	public function init() {
		if ( class_exists( '\griwpc_recaptcha' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_filter( 'comment_form_after_fields', [ $this, 'inject_lazyload_marker' ] );
		}
	}

	public function inject_lazyload_marker() {
		?>
		<span data-lazy-widget="recaptcha">&#8288;</span>
		<?php
	}
}

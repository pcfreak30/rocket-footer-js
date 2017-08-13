<?php


namespace Rocket\Footer\JS\Integration;


class McAfeeSecure extends IntegrationAbstract {

	public function init() {
		if ( class_exists( 'Mcafeesecure' ) && class_exists( 'WooCommerce' ) ) {
			remove_action( 'woocommerce_thankyou', 'Mcafeesecure::inject_sip_modal' );
			add_action( 'woocommerce_thankyou', [ $this, 'inject_sip_modal' ] );
		}
	}

	public function inject_sip_modal( $order_id ) {
		echo <<<EOT
            <script type="text/javascript" src="https://www.mcafeesecure.com/js/conversion.js" async="async"></script>
            <script type="text/javascript" class="mcafeesecure-track-conversion" data-type="purchase" data-orderid="$$order_id}" data-no-minify="1"></script>
EOT;
	}
}
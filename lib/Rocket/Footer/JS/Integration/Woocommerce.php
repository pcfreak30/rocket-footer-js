<?php


namespace Rocket\Footer\JS\Integration;


class Woocommerce extends IntegrationAbstract {

	/**
	 *
	 */
	public function init() {
		if ( class_exists( '\WooCommerce' ) ) {
			remove_action( 'user_register', 'rocket_clean_domain' );
			remove_action( 'profile_update', 'rocket_clean_domain' );
			remove_action( 'deleted_user', 'rocket_clean_domain' );
			remove_action( 'create_term', 'rocket_clean_domain' );
			remove_action( 'edited_terms', 'rocket_clean_domain' );
			remove_action( 'delete_term', 'rocket_clean_domain' );
			remove_action( 'add_link', 'rocket_clean_domain' );
			remove_action( 'edit_link', 'rocket_clean_domain' );
			remove_action( 'delete_link', 'rocket_clean_domain' );
			if ( method_exists( 'WC_Cache_Helper', 'set_nocache_constants' ) ) {
				remove_filter( 'nocache_headers', [ 'WC_Cache_Helper', 'set_nocache_constants' ] );
			}

		}
	}
}
<?php

namespace Rocket\Footer\JS\Lazyload;

/**
 * Class Manager
 *
 * @package Rocket\Footer\JS\Lazyload
 * @property array $a3_lazy_load_global_settings
 */
class Manager extends \ComposePress\Core\Abstracts\Manager {
	/**
	 * @var array
	 */
	/** @noinspection ClassOverridesFieldOfSuperClassInspection */
	protected $modules = [
		'Facebook',
		'Twitter',
		'Tumbler',
		'AvadaGoogleMaps',
		'Iframe',
		'Videos',
		'GoogleAdsenseMobile',
		'GoogleAdsense',
		'AmazonAds',
		'StumbleUpon',
		'VK',
		'BlogHerAds',
		'GoogleRemarketing',
		'PinInterest',
		'GoogleTranslate',
		'Backgroundimages',
		'GravityFormsRecaptcha',
		'Recaptcha',
		'QcodeGoogleMaps',
		'RevolutionSlider',
		'HubSpotForms',
	];

	/**
	 * @return bool
	 */
	public function is_enabled() {
		$lazy_load = false;
		if ( class_exists( 'A3_Lazy_Load' ) ) {
			$lazy_load = (bool) $this->a3_lazy_load_global_settings['a3l_apply_lazyloadxt'] && apply_filters( 'a3_lazy_load_run_filter', true );
		}
		if ( class_exists( 'LazyLoadXT' ) ) {
			$lazy_load = true;
		}

		if ( $lazy_load && class_exists( 'A3_Lazy_Load' ) && did_action( 'wp' ) ) {
			$lazy_load = has_filter( 'a3_lazy_load_html' );
			if ( $lazy_load ) {
				$lazy_load = ! $this->a3_lazy_load_excludes->check_excluded();
			}
		}

		if ( $lazy_load && did_action( 'wp_enqueue_scripts' ) ) {
			global $a3_lazy_load_global_settings;
			$dep = 'lazy-load-xt-script';
			if ( ! empty( $a3_lazy_load_global_settings ) ) {
				$dep = 'jquery-lazyloadxt';
			}

			if ( ! ( wp_script_is( $dep, 'registered' ) || wp_script_is( "{$dep}-dummy", 'registered' ) ) ) {
				$lazy_load = false;
			}
		}

		return $lazy_load;
	}
}

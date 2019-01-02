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

		return $lazy_load;
	}

}

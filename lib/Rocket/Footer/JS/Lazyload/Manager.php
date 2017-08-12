<?php

namespace Rocket\Footer\JS\Lazyload;

use Rocket\Footer\JS\ManagerAbstract;

class Manager extends ManagerAbstract {
	protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
		$modules = [
		'Facebook',
		'GooglePlus',
		'GooglePlusJS',
		'Twitter',
		'Tumbler',
		'AvadaGoogleMaps',
		'Iframe',
		'Videos',
		'GoogleAdsense',
		'AmazonAds',
		'StumbleUpon',
		'VK',
		'BlogHerAds',
		'GoogleRemarketing',
		'PinInterest',
		'GoogleTranslate',
	];

	public function is_enabled() {
		global $a3_lazy_load_global_settings;
		$lazy_load = false;
		if ( class_exists( 'A3_Lazy_Load' ) ) {
			$lazy_load = (bool) $a3_lazy_load_global_settings['a3l_apply_lazyloadxt'];
		}
		if ( class_exists( 'LazyLoadXT' ) ) {
			$lazy_load = true;
		}

		return $lazy_load;
	}

}
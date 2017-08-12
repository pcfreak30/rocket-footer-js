<?php

namespace Rocket\Footer\JS\Rewrite;

use Rocket\Footer\JS\ManagerAbstract;

class Manager extends ManagerAbstract {
	protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
		$modules = [
		'Tawkto',
		'WPRocketLazyload',
		'GoogleAnalytics',
		'DoubleClickGoogleAnalytics',
		'FacebookPixel',
		'GoogleWebFonts',
		'SumoMe',
		'Avvo',
		'PushCrew',
		'MouseFlow',
		'Clicky',
		'GoogleTagManager',
		'McAfeeSecure',
		'HubSpot',
		'PingdomPrum',
	];
}
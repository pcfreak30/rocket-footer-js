<?php

namespace Rocket\Footer\JS\Rewrite;

use pcfreak30\WordPress\Plugin\Framework\ManagerAbstract;

/**
 * Class Manager
 *
 * @package Rocket\Footer\JS\Rewrite
 */
class Manager extends ManagerAbstract {
	/** @noinspection ClassOverridesFieldOfSuperClassInspection */
	protected $modules = [
		'AsyncDocumentWrite',
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
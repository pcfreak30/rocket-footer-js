<?php

namespace Rocket\Footer\JS\Rewrite;

use Rocket\Footer\JS\ManagerAbstract;

class Manager extends ManagerAbstract {
	protected $modules = [
		'Tawkto',
		'WPRocketLazyload',
		'GoogleAnalytics',
		'DoubleClickGoogleAnalytics',
	];
}
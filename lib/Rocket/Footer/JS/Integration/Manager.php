<?php

namespace Rocket\Footer\JS\Integration;


use Rocket\Footer\JS\ManagerAbstract;

class Manager extends ManagerAbstract {
	protected /** @noinspection ClassOverridesFieldOfSuperClassInspection */
		$modules = [
		'Amp',
		'PageLinksTo',
		'NExtend',
		'VidBgPro',
		'Cornerstone',
		'GoogleAnalytics',
		'PixelYourSite',
		'WoocommerceSocialMediaSharesButtons',
		'RevolutionSlider',
	];

}
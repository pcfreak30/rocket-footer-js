<?php

namespace Rocket\Footer\JS\Integration;


use pcfreak30\WordPress\Plugin\Framework\ManagerAbstract;

class Manager extends ManagerAbstract {
	/** @noinspection ClassOverridesFieldOfSuperClassInspection */
	protected $modules = [
		'Amp',
		'PageLinksTo',
		'NExtend',
		'VidBgPro',
		'Cornerstone',
		'GoogleAnalytics',
		'PixelYourSite',
		'WoocommerceSocialMediaSharesButtons',
		'RevolutionSlider',
		'McAfeeSecure',
	];

}
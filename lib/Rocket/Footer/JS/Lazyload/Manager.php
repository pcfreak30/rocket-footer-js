<?php

namespace Rocket\Footer\JS\LazyLoad;

use Rocket\Footer\JS\ManagerAbstract;

class Manager extends ManagerAbstract {
	protected $modules = [
		'Facebook',
		'GooglePlus',
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
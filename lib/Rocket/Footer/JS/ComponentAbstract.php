<?php

namespace Rocket\Footer\JS;

/**
 * Class ComponentAbstact
 *
 * @package Rocket\Footer\JS
 */
abstract class ComponentAbstract {
	/**
	 * @var \Rocket\Footer\JS
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $settings = [];

	/**
	 *
	 */
	public function init() {

	}

	/**
	 *
	 */
	public function __destruct() {
		$this->app = null;
	}

	/**
	 * @param $app
	 */
	public function set_app( $app ) {
		$this->app = $app;
	}
}
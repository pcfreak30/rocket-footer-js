<?php


namespace Rocket\Footer\JS;


class ManagerAbstract extends ComponentAbstract {
	protected $namespace;
	protected $modules = [

	];

	public function __construct() {
		$this->namespace = ( new \ReflectionClass( get_called_class() ) )->getNamespaceName();
	}

	public function init() {
		parent::init();
		$modules = [];

		$reflect   = new \ReflectionClass( $this );
		$class     = strtolower( $reflect->getShortName() );
		$namespace = $reflect->getNamespaceName();
		$namespace = str_replace( '\\', '/', $namespace );
		$component = strtolower( basename( $namespace ) );
		$filter    = "rocket_footer_js_{$component}_{$class}_modules";

		foreach ( (array) apply_filters( $filter, $this->modules ) as $module ) {
			$modules[ $module ] = rocket_footer_js_container()->create( $this->namespace . '\\' . $module );
			$modules[ $module ]->init();
		}
		$this->modules = $modules;
	}

	/**
	 * @return array
	 */
	public function get_modules() {
		return $this->modules;
	}

}
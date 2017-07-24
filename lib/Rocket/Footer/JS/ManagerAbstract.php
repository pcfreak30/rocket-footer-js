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

		$modules_list = apply_filters( $filter, $this->modules );

		foreach ( $modules_list as $module ) {
			$modules[ $module ] = rocket_footer_js_container()->create( $this->namespace . '\\' . $module );
		}
		foreach ( $modules_list as $module ) {
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

	/**
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public function get_module( $name ) {
		foreach ( $this->modules as $module ) {
			if ( is_a( $module, ( new \ReflectionClass( $this ) )->getNamespaceName() . '\\' . $name ) ) {
				return $module;
			}
		}

		return false;
	}

}
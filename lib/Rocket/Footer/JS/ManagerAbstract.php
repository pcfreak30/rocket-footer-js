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
		foreach ( $this->modules as $module ) {
			$modules[ $module ] = rocket_footer_js_container()->create( $this->namespace . '\\' . $module );
			$modules[ $module ]->init();
		}
		$this->modules = $modules;
	}

}
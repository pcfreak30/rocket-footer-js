<?php

/* @var $container \Dice\Dice */

$container->addRule( '\\Rocket\\Footer\\JS', [
	'shared' => true,
] );
$container->addRule( '\\Rocket\\Footer\\JS\\DOMDocument', [
	'call' => [
		[ 'registerNodeClass', [ 'DOMElement', '\\Rocket\\Footer\\JS\\DOMElement' ] ],
	],
] );
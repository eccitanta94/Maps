<?php
/**
 * Copyright (c) 2015 Petr Olišar (http://olisar.eu)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace Jashin\Maps;


/**
 * Description of MapApiExtension
 *
 * @author Nikolaj Pognerebko <pognerebko@icloud.com>
 */
class MapApiExtension extends \Nette\DI\CompilerExtension
{

	public $defaults = array(
		'key' => NULL,
		'width' => '100%',
		'height' => '100%',
		'waypointlimit' => 8,
		'zoom' => 7,
		'coordinates' => array(),
		'type' => 'ROADMAP',
		'scrollable' => true,
		'static' => false,
			'preserveViewport' => false,
		'markers' => array(
			'bound' => false,
			'markerClusterer' => false,
			'iconDefaultPath' => NULL,
			'icon' => NULL,
			'addMarkers' => array()
		),
		'waypoints' => array(
			'addWaypoints' => array()
		)
	);


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('mapAPI'))
			->setImplement('Jashin\Maps\IMapAPI')
			->setFactory('Jashin\Maps\MapAPI')
			->addSetup('setup', array($config))
			->addSetup('setKey', array($config['key']))
			->addSetup('setCoordinates', array($config['coordinates']))
			->addSetup('setType', array($config['type']))
			->addSetup('isStaticMap', array($config['static']))
			->addSetup('isScrollable', array($config['scrollable']))
			->addSetup('setZoom', array($config['zoom']));

		$builder->addDefinition($this->prefix('markers'))
			->setImplement('Jashin\Maps\IMarkers')
			->setFactory('Jashin\Maps\Markers')
			->addSetup('setDefaultIconPath', array($config['markers']['iconDefaultPath']))
			->addSetup('fitBounds', array($config['markers']['bound']))
			->addSetup('isMarkerClusterer', array($config['markers']['markerClusterer']))
			->addSetup('addMarkers', array($config['markers']['addMarkers']));

		$builder->addDefinition($this->prefix('waypoints'))
			->setImplement('Jashin\Maps\IWaypoints')
			->setFactory('Jashin\Maps\Waypoints')
			->addSetup('addWaypoints', array($config['waypoints']['addWaypoints']));
	}

}

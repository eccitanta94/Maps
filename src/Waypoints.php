<?php
/**
 * Copyright (c) 2015 Petr Olišar (http://olisar.eu)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace Jashin\Maps;

/**
 * Description of Markers
 *
 * @author Nikolaj Pognerebko <pognerebko@icloud.com>
 */
class Waypoints extends \Nette\Object
{


	/**
	 * @var \Nette\Http\SessionSection
	 */
	private $section;

	/**
	 * Markers constructor.
	 * @param \Nette\Http\Session $session
	 */
	public function __construct(\Nette\Http\Session $session)
	{
		$this->section = $session->getSection('jasin755/Maps');
	}

	/**
	 * @internal
	 * @param array $waypoints
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setWaypoints(array $waypoints)
	{
		$this->deleteWaypoints();
		$this->addWaypoints($waypoints);
	}

	/**
	 * @internal
	 * @param array $waypoints
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addWaypoints(array $waypoints)
	{
		if (count($waypoints)) {
			foreach ($waypoints as $waypoint) {
				$this->createWaypoint($waypoint);
			}
		}
	}

	/**
	 * @param array $position
	 * @return Markers
	 */
	public function addWaypoint(array $position, $identifier = NULL)
	{
		$this->section->waypoints[] = array(
				'position' => $position,
				'identifier' => $identifier
		);
		return $this;
	}


	public function getWaypoint()
	{
		return end($this->section->waypoints);
	}

	/**
	 * @return array
	 */
	public function getWaypoints()
	{
		return $this->section->waypoints;
	}


	public function deleteWaypoints()
	{
		$this->section->waypoints = array();
	}


	private function createWaypoint(array $waypoint)
	{
		if (!array_key_exists('coordinates', $waypoint)) {
			throw new \Nette\InvalidArgumentException('Coordinates must be set in every marker');
		}

		$this->addWaypoint(array_values($waypoint['coordinates']));
	}
}

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
class Markers extends \Nette\Object
{

	const DROP = 'DROP', BOUNCE = 'BOUNCE';

	/** @var String */
	private $iconDefaultPath;

	/** @var Boolean */
	private $bound = FALSE;

	/** @var Boolean */
	private $markerClusterer = FALSE;

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
	 * @param array $markers
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setMarkers(array $markers)
	{
		$this->deleteMarkers();
		$this->addMarkers($markers);
	}

	/**
	 * @internal
	 * @param array $markers
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addMarkers(array $markers)
	{
		if (count($markers)) {
			foreach ($markers as $marker) {
				$this->createMarker($marker);
			}
		}
	}

	/**
	 * @param array $position
	 * @param boolean $animation
	 * @param String $title
	 * @return Markers
	 */
	public function addMarker(array $position, $identifier = NULL, $animation = false, $title = NULL, $color = NULL)
	{
		if (!is_string($animation) && !is_bool($animation)) {
			throw new \InvalidArgumentException("Animation must be string or boolean, $animation (" .
				gettype($animation) . ") was given");
		}

		$this->section->markers[] = array(
				'position' => $position,
				'identifier' => $identifier,
				'title' => $title,
				'animation' => $animation,
				'visible' => true,
				'color' => $color
		);
		return $this;
	}


	public function getMarker()
	{
		return end($this->section->markers);
	}

	/**
	 * @return array
	 */
	public function getMarkers()
	{
		return $this->section->markers;
	}


	public function deleteMarkers()
	{
		$this->section->markers = array();
	}


	/**
	 *
	 * @param Boolean $cluster
	 * @return \Jashin\Maps\Markers
	 * @throws \InvalidArgumentException
	 */
	public function isMarkerClusterer($cluster = true)
	{
		if (!is_bool($cluster)) {
			throw new \InvalidArgumentException("cluster must be boolean, $cluster (" . gettype($cluster) . ") was given");
		}

		$this->markerClusterer = $cluster;
		return $this;
	}


	/**
	 *
	 * @return Boolean
	 */
	public function getMarkerClusterer()
	{
		return $this->markerClusterer;
	}


	/**
	 * @param Boolean $bound Show all of markers
	 * @return \Jashin\Maps\MapAPI
	 */
	public function fitBounds($bound = true)
	{
		if (!is_bool($bound)) {
			throw new \InvalidArgumentException("fitBounds must be boolean, $bound (" . gettype($bound) . ") was given");
		}

		$this->bound = $bound;
		return $this;
	}


	/**
	 *
	 * @return Boolean
	 */
	public function getBound()
	{
		return $this->bound;
	}


	/**
	 *
	 * @param Marker\Icon | String $icon
	 */
	public function setIcon($icon)
	{
		end($this->section->markers);         // move the internal pointer to the end of the array
		$key = key($this->section->markers);
		if ($icon instanceof Marker\Icon) {
			$icon->setUrl(is_null($this->iconDefaultPath) ? $icon->getUrl() : $this->iconDefaultPath . $icon->getUrl());
			$this->section->markers[$key]['icon'] = $icon->getArray();

		} else {
			$this->section->markers[$key]['icon'] = is_null($this->iconDefaultPath) ? $icon : $this->iconDefaultPath . $icon;
		}

		return $this;
	}


	/**
	 *
	 * @param String $defaultPath
	 * @return \Jashin\Maps\Markers
	 */
	public function setDefaultIconPath($defaultPath)
	{
		if (!is_null($defaultPath) &&
			!\Nette\Utils\Strings::endsWith($defaultPath, '/') &&
			!\Nette\Utils\Strings::endsWith($defaultPath, '\\')
		) {
			$defaultPath .= DIRECTORY_SEPARATOR;
		}
		$this->iconDefaultPath = $defaultPath;
		return $this;
	}


	public function getDefaultIconPath()
	{
		return $this->iconDefaultPath;
	}


	/**
	 *
	 * @param String $color Color can be 24-bit color or: green, purple, yellow, blue, gray, orange, red
	 * @return \Jashin\Maps\Markers
	 */
	public function setColor($color)
	{
		$allowed = array('green', 'purple', 'yellow', 'blue', 'orange', 'red');
		if (!in_array($color, $allowed)) {
			if (!\Nette\Utils\Strings::match($color, '~^0x[a-f0-9]{6}$~i')) {
				throw new \Nette\InvalidArgumentException('Color must be 24-bit color or from the allowed list.');
			}
		}
		end($this->section->markers);         // move the internal pointer to the end of the array
		$key = key($this->section->markers);
		$this->section->markers[$key]['color'] = $color;
		return $this;
	}


	private function createMarker(array $marker)
	{
		if (!array_key_exists('coordinates', $marker)) {
			throw new \Nette\InvalidArgumentException('Coordinates must be set in every marker');
		}

		$this->addMarker(array_values($marker['coordinates']),
			isset($marker['animation']) ? $marker['animation'] : false,
			isset($marker['title']) ? $marker['title'] : NULL);

		if (array_key_exists('message', $marker)) {
			if (is_array($marker['message'])) {
				$message = array_values($marker['message']);
				$this->setMessage($message[0], $message[1]);
			} else {
				$this->setMessage($marker['message']);
			}
		}

		if (array_key_exists('icon', $marker)) {
			if (is_array($marker['icon'])) {
				$icon = new Marker\Icon($marker['icon']['url']);

				if (array_key_exists('size', $marker['icon'])) {
					$icon->setSize($marker['icon']['size']);
				}

				if (array_key_exists('anchor', $marker['icon'])) {
					$icon->setAnchor($marker['icon']['anchor']);
				}

				if (array_key_exists('origin', $marker['icon'])) {
					$icon->setOrigin($marker['icon']['origin']);
				}
				$this->setIcon($icon);

			} else {
				$this->setIcon($marker['icon']);
			}
		}

		if (array_key_exists('color', $marker)) {
			$this->setColor($marker['color']);
		}
	}
}

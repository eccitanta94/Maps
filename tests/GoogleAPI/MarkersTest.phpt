<?php
/**
 * Created by PhpStorm.
 * User: petr
 * Date: 8.11.15
 * Time: 23:06
 */

namespace Jashin\Maps;


use Tester\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class MarkersTest extends TestCase
{

	/**
	 * @var Markers
	 */
	private $markers;


	protected function setUp()
	{
		parent::setUp();
		$this->markers = new Markers();
	}


	public function testIsMarkerClusterer()
	{
		Assert::false($this->markers->getMarkerClusterer());

		$this->markers->isMarkerClusterer();
		Assert::true($this->markers->getMarkerClusterer());

		$this->markers->isMarkerClusterer(FALSE);
		Assert::false($this->markers->getMarkerClusterer());

		$this->markers->isMarkerClusterer(TRUE);
		Assert::true($this->markers->getMarkerClusterer());

		Assert::exception(function () {
			$this->markers->isMarkerClusterer('foo');
		}, \InvalidArgumentException::class, 'cluster must be boolean, foo (string) was given');
	}


	public function testDefaultIconPath()
	{
		$this->markers->setDefaultIconPath('foo/bar');
		Assert::same('foo/bar/', $this->markers->getDefaultIconPath());

		$this->markers->setDefaultIconPath('foo/bar/');
		Assert::same('foo/bar/', $this->markers->getDefaultIconPath());
	}


	public function testFitBounds()
	{
		Assert::false($this->markers->getBound());

		$this->markers->fitBounds();
		Assert::true($this->markers->getBound());

		$this->markers->fitBounds(FALSE);
		Assert::false($this->markers->getBound());

		$this->markers->fitBounds(TRUE);
		Assert::true($this->markers->getBound());

		Assert::exception(function () {
			$this->markers->fitBounds('foo');
		}, \InvalidArgumentException::class, 'fitBounds must be boolean, foo (string) was given');
	}


	public function testAddMarker()
	{
		$this->markers->addMarker([11, 12]);
		Assert::equal([
			'position' => [11, 12],
			'title' => NULL,
			'animation' => FALSE,
			'visible' => TRUE
		], $this->markers->getMarker());

		$this->markers->addMarker([21, 22], Markers::BOUNCE);
		Assert::equal([
				'position' => [21, 22],
				'title' => NULL,
				'animation' => 'BOUNCE',
				'visible' => TRUE
		], $this->markers->getMarker());

		$this->markers->addMarker([31, 32], Markers::DROP);
		Assert::equal([
				'position' => [31, 32],
				'title' => NULL,
				'animation' => 'DROP',
				'visible' => TRUE
		], $this->markers->getMarker());

		$this->markers->addMarker([41, 42], FALSE, 'foo');
		Assert::equal([
				'position' => [41, 42],
				'title' => 'foo',
				'animation' => FALSE,
				'visible' => TRUE
		], $this->markers->getMarker());

		Assert::exception(function () {
			$this->markers->addMarker([], 123);
		}, \InvalidArgumentException::class);

		Assert::exception(function () {
			$this->markers->addMarker([], FALSE, 123);
		}, \InvalidArgumentException::class);

		Assert::equal([
				[
						'position' => [11, 12],
						'title' => NULL,
						'animation' => FALSE,
						'visible' => TRUE,
				],
				[
						'position' => [21, 22],
						'title' => NULL,
						'animation' => 'BOUNCE',
						'visible' => TRUE,
				],
				[
						'position' => [31, 32],
						'title' => NULL,
						'animation' => 'DROP',
						'visible' => TRUE,
				],
				[
						'position' => [41, 42],
						'title' => 'foo',
						'animation' => FALSE,
						'visible' => TRUE,
				],
		], $this->markers->getMarkers());

		$this->markers->deleteMarkers();
		Assert::same([], $this->markers->getMarkers());
		Assert::false($this->markers->getMarker());
	}


	public function testSetMessage()
	{
		Assert::exception(function () {
			$this->markers->setMessage('foo');
		}, \InvalidArgumentException::class, 'setMessage must be called after addMarker()');
	}

}

$test = new MarkersTest();
$test->run();
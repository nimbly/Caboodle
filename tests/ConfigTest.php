<?php

namespace Caboodle\Tests;

use Caboodle\Config;
use Caboodle\KeyNotFoundException;
use Caboodle\Loaders\FileLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers Caboodle\Config
 * @covers Caboodle\Loaders\FileLoader
 * @covers Caboodle\ConfigException
 * @covers Caboodle\KeyNotFoundException
 *
 * @uses Caboodle\Loaders\LoaderInterface
 */
class ConfigTest extends TestCase
{
	public function test_constructor_sets_loaders()
	{
		$fileLoader = new FileLoader(__DIR__ . "/config");

		$config = new Config([$fileLoader]);

		$reflection = new \ReflectionClass($config);
		$property = $reflection->getProperty('loaders');
        $property->setAccessible(true);

		$this->assertEquals([
			$fileLoader
		], $property->getValue($config));
	}

	public function test_add_loader()
	{
		$config = new Config([new FileLoader(__DIR__)]);

		$fileLoader = new FileLoader(__DIR__);
		$config->addLoader($fileLoader);

		$reflection = new \ReflectionClass($config);
		$property = $reflection->getProperty('loaders');
        $property->setAccessible(true);

		$this->assertEquals([
			$fileLoader,
			$fileLoader
		], $property->getValue($config));

		$this->assertSame(
			$fileLoader,
			$property->getValue($config)[1]
		);
	}

	public function test_set_items()
	{
		$config = new Config;

		$items = [
			'key1' => 'value1',
			'key2' => [
				'key3' => 'value3'
			]
		];

		$config->setItems($items);

		$this->assertEquals($items, $config->all());
	}

	public function test_set()
	{
		$config = new Config;
		$config->set('foo', 'bar');

		$this->assertEquals('bar', $config->get('foo'));
	}

	public function test_all()
	{
		$config = new Config;
		$config->set('foo', 'bar');

		$this->assertEquals(
			[
				"foo" => "bar"
			],
			$config->all()
		);
	}

	public function test_auto_loading()
	{
		$config = new Config([
			new FileLoader(__DIR__ . "/config")
		]);

		$this->assertEquals(
			[
				"key1" => "value1",
				"key2" => [
					"key2_key1" => "value1"
				],
				"key3" => [
					"key3_key1" => [
						"key3_key1_key1" => "value1"
					]
				]
			],
			$config->get('example')
		);
	}

	public function test_getting_non_existant_value_returns_null()
	{
		$config = new Config;

		$this->assertNull(
			$config->get("database.host")
		);
	}

	public function test_set_throw_if_not_found()
	{
		$config = new Config;
		$config->setThrowIfNotFound(true);

		$this->expectException(KeyNotFoundException::class);
		$config->get('example');

		$config->get("#production.db.default#.host");
	}
}
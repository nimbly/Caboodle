<?php

namespace nimbly\Config\Tests;

use nimbly\Config\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers nimbly\Config\Config
 */
class ConfigTest extends TestCase
{
	public function test_constructor_sets_config_path()
	{
		$config = new Config(__DIR__ . "/config");

		$reflection = new \ReflectionClass($config);

		$property = $reflection->getProperty('path');
        $property->setAccessible(true);
		$path = $property->getValue($config);

		$this->assertEquals(__DIR__ . "/config", $path);
	}

	public function test_set()
	{
		$config = new Config(__DIR__ . "/config");

		$config->set('foo', 'bar');

		$this->assertEquals('bar', $config->get('foo'));
	}

	public function test_all()
	{
		$config = new Config(__DIR__ . "/config");
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
		$config = new Config(__DIR__ . "/config");

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

	public function test_getting_non_existant_value_returns_default()
	{
		$config = new Config(__DIR__ . "/config");

		$this->assertEquals(
			"default",
			$config->get("database.host", "default")
		);
	}
}
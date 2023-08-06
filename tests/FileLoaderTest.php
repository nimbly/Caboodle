<?php

namespace Caboodle\Tests;

use Nimbly\Caboodle\Loaders\FileLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers Caboodle\Loaders\FileLoader
 */
class FileLoaderTest extends TestCase
{
	public function test_constructor()
	{
		$loader = new FileLoader(__DIR__);

		$reflection = new \ReflectionClass($loader);
		$property = $reflection->getProperty('path');
		$property->setAccessible(true);

		$this->assertEquals(
			__DIR__,
			$property->getValue($loader)
		);
	}

	public function test_invalid_key_returns_null()
	{
		$loader = new FileLoader(__DIR__);

		$this->assertNull(
			$loader->load(".")
		);
	}

	public function test_file_not_found_returns_null()
	{
		$loader = new FileLoader(__DIR__);

		$this->assertNull(
			$loader->load("example")
		);
	}

	public function test_file_found_returns_contents_of_file()
	{
		$loader = new FileLoader(__DIR__ . "/config");

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
			$loader->load("example")
		);
	}
}
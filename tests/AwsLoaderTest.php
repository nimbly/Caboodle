<?php

namespace nimbly\Config\Tests;

use Aws\SecretsManager\SecretsManagerClient;
use nimbly\Config\AwsLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers nimbly\Config\AwsLoader
 */
class AwsLoaderTest extends TestCase
{
	public function test_constructor()
	{
		$secretsManager = new SecretsManagerClient([
			'version' => 'latest',
			'region' => 'us-west-2'
		]);

		$loader = new AwsLoader($secretsManager);

		$reflection = new \ReflectionClass($loader);
		$property = $reflection->getProperty('client');
		$property->setAccessible(true);

		$this->assertSame(
			$secretsManager,
			$property->getValue($loader)
		);
	}

	public function test_invalid_key_returns_null()
	{
		$loader = new AwsLoader(new SecretsManagerClient([
			'version' => 'latest',
			'region' => 'us-west-2'
		]));

		$this->assertNull(
			$loader->load(".")
		);
	}
}
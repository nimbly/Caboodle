<?php

namespace Caboodle\Tests;

use Aws\SecretsManager\SecretsManagerClient;
use Nimbly\Caboodle\Loaders\AwsLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers Caboodle\Loaders\AwsLoader
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
}
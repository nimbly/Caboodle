<?php

namespace nimbly\Config;

use Aws\SecretsManager\Exception\SecretsManagerException;
use Aws\SecretsManager\SecretsManagerClient;


class AwsLoader implements LoaderInterface
{
	/**
	 * SecretsManagerClient instance.
	 *
	 * @var SecretsManagerClient
	 */
	protected $client;

	/**
	 * AwsLoader constructor.
	 *
	 * @param SecretsManagerClient $client
	 */
	public function __construct(SecretsManagerClient $client)
	{
		$this->client = $client;
	}

	/**
	 * @inheritDoc
	 */
	public function load(string $key): ?array
	{
		if( \preg_match("/^([^\.]+)\.?/", $key, $match) === false ){
			return null;
		}

		$key = $match[1];

		try {

			$response = $this->client->GetSecretValue([
				'SecretId' => $key
			]);

		}
		catch( SecretsManagerException $exception ) {
			return null;
		}

		return [$key => \json_decode($response->get('SecretString'), true)];
	}
}
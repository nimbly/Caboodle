<?php

namespace Caboodle\Loaders;

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
		try {

			$response = $this->client->GetSecretValue([
				'SecretId' => $key
			]);

		}
		catch( SecretsManagerException $exception ) {
			return null;
		}

		$secret = $response->get('SecretString');

		if( empty($secret) ){
			return null;
		}

		return \json_decode($secret, true);
	}
}
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
	public function load(string $key, array $options = []): ?array
	{
		try {

			$response = $this->client->GetSecretValue(
				\array_merge([
					'SecretId' => $key
				], $options)
			);

		}
		catch( SecretsManagerException $exception ) {
			return null;
		}

		return \json_decode($response->get('SecretString'), true);
	}
}
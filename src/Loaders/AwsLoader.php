<?php

namespace Nimbly\Caboodle\Loaders;

use Aws\SecretsManager\Exception\SecretsManagerException;
use Aws\SecretsManager\SecretsManagerClient;
use Nimbly\Caboodle\LoaderInterface;

class AwsLoader implements LoaderInterface
{
	/**
	 * @param SecretsManagerClient $client SecretsManagerClient instance.
	 */
	public function __construct(
		protected SecretsManagerClient $client)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function load(string $key): ?array
	{
		try {

			$response = $this->client->GetSecretValue([
				"SecretId" => $key
			]);

		}
		catch( SecretsManagerException ) {
			return null;
		}

		$secret = $response->get("SecretString");

		if( empty($secret) ){
			return null;
		}

		return \json_decode($secret, true);
	}
}
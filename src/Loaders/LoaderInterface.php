<?php

namespace Caboodle\Loaders;

interface LoaderInterface
{
	/**
	 * Load a key from the storage provider.
	 *
	 * @param string $key
	 * @return array<string, mixed>|null
	 */
	public function load(string $key): ?array;
}
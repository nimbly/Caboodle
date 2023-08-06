<?php

namespace Nimbly\Caboodle;

interface LoaderInterface
{
	/**
	 * Load a key from the storage provider.
	 *
	 * @param string $key
	 * @return array<array-key,mixed>|null
	 */
	public function load(string $key): ?array;
}
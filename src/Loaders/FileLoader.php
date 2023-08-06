<?php

namespace Nimbly\Caboodle\Loaders;

use Nimbly\Caboodle\LoaderInterface;

class FileLoader implements LoaderInterface
{
	/**
	 * @param string $path Path where configutation files can be found.
	 */
	public function __construct(
		protected string $path)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function load(string $key): ?array
	{
		$file = "{$this->path}/{$key}.php";

		if( \file_exists($file) === false ){
			return null;
		}

		return include $file;
	}
}
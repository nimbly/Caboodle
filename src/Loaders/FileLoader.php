<?php

namespace Caboodle\Loaders;


class FileLoader implements LoaderInterface
{
	/**
	 * The base path where the config files can be found.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * FileLoader constructor.
	 *
	 * @param string $path
	 */
	public function __construct(string $path)
	{
		$this->path = $path;
	}

	/**
     * @inheritDoc
     */
    public function load(string $key, array $options = []): ?array
    {
		$file = "{$this->path}/{$key}.php";

		if( \file_exists($file) === false ){
			return null;
		}

		return include $file;
	}
}
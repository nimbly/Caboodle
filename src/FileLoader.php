<?php

namespace nimbly\Config;

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
    public function load(string $key): ?array
    {
        if( \preg_match("/^([^\.]+)\.?/", $key, $match) == false ){
			return null;
		}

		$key = $match[1];
		$file = "{$this->path}/{$key}.php";

		if( \file_exists($file) === false ){
			return null;
		}

		return [$key => include $file];
	}
}
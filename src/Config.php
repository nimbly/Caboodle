<?php

namespace nimbly\Config;


class Config
{
    /**
     * Path on disk where config files are held.
     *
     * @var string
     */
    protected $path;

    /**
     * Config items.
     *
     * @var array<string, mixed>
     */
    protected $items = [];

    /**
     * Config constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
	}

    /**
     * Get all config entries loaded.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Resolve the flattened key into the actual value.
     *
     * @param string $key
     * @throws \Exception
     * @return mixed
     */
    protected function resolve(string $key)
    {
        // Set the pointer at the root of the items array
        $pointer = &$this->items;

        /**
         *
         * Loop through all the parts and see if the key exists.
         *
         */
        foreach( \explode(".", $key) as $part ){

            if( \array_key_exists($part, $pointer) === false ){
                throw new ConfigException("Config key {$key} not found.");
            }

            $pointer = &$pointer[$part];
        }

        return $pointer;
    }

    /**
     * See if config has given key.
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        try {

            $this->resolve($key);

        } catch( ConfigException $exception ){

            return false;
        }

        return true;
    }

    /**
     * Lazy load configuration files.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if( $this->has($key) === false ){

            $this->loadFromFile($key);
        }

        try {

            $configValue = $this->resolve($key);

        } catch ( ConfigException $exception ){

            return $default;

        }

        return $configValue;
    }

    /**
     * Set a key/value pair.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Load a file and assign it to the key.
     *
     * @param string $key
     * @return void
     */
    protected function loadFromFile(string $key): void
    {
        // Auto resolve filename
        if( \preg_match("/^([^\.]+)\.?/", $key, $match) ){

            $key = $match[1];

            $file = "{$this->path}/{$key}.php";

            // Check for file's existence
            if( \file_exists($file) === false ){
				return;
            }

            // Pull config file in and add values into master config
            $this->set($key, include $file);
        }

	}
}
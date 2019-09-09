<?php

namespace Caboodle;

use Caboodle\Loaders\LoaderInterface;


class Config
{
	/**
	 * Set of LoaderInterfaces to resolve config names.
	 *
	 * @var array<LoaderInterface>
	 */
	protected $loaders;

    /**
     * Config items.
     *
     * @var array<string, mixed>
     */
	protected $items = [];

	/**
	 * Throw exception if value not found.
	 *
	 * @var boolean
	 */
	protected $throwIfNotFound = false;

    /**
     * Config constructor.
     *
     * @param array<LoaderInterface> $loaders
     */
    public function __construct(array $loaders = [])
    {
        $this->loaders = $loaders;
	}

	/**
	 * Enable/disable throwing an exception if a key is not found.
	 *
	 * @param boolean $throwIfNotFound
	 * @return void
	 */
	public function setThrowIfNotFound(bool $throwIfNotFound = true): void
	{
		$this->throwIfNotFound = $throwIfNotFound;
	}

	/**
	 * Add a loader.
	 *
	 * @param LoaderInterface $loader
	 * @return void
	 */
	public function addLoader(LoaderInterface $loader): void
	{
		$this->loaders[] = $loader;
	}

	/**
	 * Sets the items loaded. This will overwrite *all* currently loaded items.
	 *
	 * This method is ideal for loading items directly in from cache.
	 *
	 * @param array<string, mixed> $items
	 * @return void
	 */
	public function setItems(array $items): void
	{
		$this->items = $items;
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
     * Resolve a key and path into a value.
     *
     * @param string $index
	 * @param string|null $path
     * @throws KeyNotFoundException
     * @return mixed
     */
    protected function resolve(string $index, ?string $path = null)
    {
        // Set the pointer at the specified key.
		$pointer = &$this->items[$index] ?? null;

		if( empty($pointer) ){
			throw new KeyNotFoundException("Key not found.");
		}

		// Break apart path dotted notation to traverse item store.
        foreach( \explode(".", $path ?? "") as $part ){

			if( empty($part) ){
				continue;
			}

            if( \array_key_exists($part, $pointer) === false ){
                throw new KeyNotFoundException("Key not found.");
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

			list($index, $path) = $this->parseKey($key);

            $this->resolve($index, $path);

        } catch( KeyNotFoundException $exception ){

            return false;
        }

        return true;
    }

    /**
     * Get a configuration value.
	 *
	 * Use dotted notation to get specific values. Eg, "database.connections.default.host"
	 *
	 * Returns *null* if key not found.
     *
     * @param string $key
     * @param array $options
	 * @throws KeyNotFoundException
     * @return mixed
     */
    public function get(string $key, array $options = [])
    {
		list($index, $path) = $this->parseKey($key);

		// If the key does not exist, try loading.
        if( $this->has($key) === false ){
			$this->load($index, $options);
        }

		// Try resolving now.
        try {

			$value = $this->resolve($index, $path);

        } catch( KeyNotFoundException $exception ){

			if( $this->throwIfNotFound ){
				throw $exception;
			}

            return null;
        }

        return $value;
    }

    /**
     * Set a key/value pair directly.
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
	 * Loop through loaders and attempt to load configuration data.
	 *
	 * Stop on the first loader that succeeds.
	 *
	 * @param string $index
	 * @return void
	 */
	private function load(string $index, array $options = []): void
	{
		foreach( $this->loaders as $loader ){
			if( ($items = \call_user_func_array([$loader, 'load'], [$index, $options])) ){
				$this->items[$index] = $items;
				break;
			}
		}
	}

	/**
	 * Parse a key into index and path values.
	 *
	 * @param string $key
	 * @return array
	 */
	protected function parseKey(string $key): array
	{
		// Key hint/tag.
		if( \preg_match("/^([^\#]+)\#(.*)$/", $key, $match) ){
			return [$match[1], $match[2]];
		}

		// Standard dotted notation.
		elseif( \preg_match("/^([^\.]+)\.?(.*)$/", $key, $match) ){
			return [$match[1], $match[2]];
		}

		return [null, null];
	}
}
<?php

namespace Caboodle;

use Caboodle\Loaders\LoaderInterface;
use Psr\Container\ContainerInterface;

class Config implements ContainerInterface
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
	 * Throw a KeyNotFoundException when config key
	 * is not found instead of a null.
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
	 * Enable/disable throwing KeyNotFoundException when getting a key
	 * that does not exist.
	 *
	 * @param boolean $enable
	 * @return void
	 */
	public function setThrowIfNotFound(bool $enable): void
	{
		$this->throwIfNotFound = $enable;
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
     * Test if config has given key.
     *
     * @param string $key
     * @return boolean
     */
    public function has($key): bool
    {
        try {

			list($index, $path) = $this->parseKey($key);

            $this->resolve($index, $path);

        } catch( KeyNotFoundException $e ){

            return false;
        }

        return true;
    }

    /**
     * Get a configuration value.
	 *
	 * Use dotted notation to get specific values. Eg, "database.connections.default.host"
     *
     * @param string $key
	 * @throws KeyNotFoundException
     * @return mixed
     */
    public function get($key)
    {
		list($index, $path) = $this->parseKey($key);

		// If the key does not exist, try loading.
        if( $this->has($key) === false ){
			$this->load($index);
        }

		try {

			$value = $this->resolve($index, $path);

		} catch( KeyNotFoundException $keyNotFoundException ){

			if( $this->throwIfNotFound ){
				throw $keyNotFoundException;
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
	private function load(string $index): void
	{
		foreach( $this->loaders as $loader ){
			if( ($items = \call_user_func([$loader, 'load'], $index)) ){
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
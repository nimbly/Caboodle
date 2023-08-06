<?php

namespace Nimbly\Caboodle;

use Psr\Container\ContainerInterface;
class Config implements ContainerInterface
{
	/**
	 * Config items.
	 *
	 * @var array<array-key,mixed>
	 */
	protected array $items = [];

	/**
	 * @param array<LoaderInterface> $loaders
	 */
	public function __construct(
		protected array $loaders = [],
		protected bool $throwIfNotFound = false)
	{
	}

	/**
	 * Sets the items loaded. This will overwrite *all* currently loaded items.
	 *
	 * This method is ideal for loading items directly in from cache.
	 *
	 * @param array<array-key,mixed> $items
	 * @return void
	 */
	public function setItems(array $items): void
	{
		$this->items = $items;
	}

	/**
	 * Get all config entries loaded.
	 *
	 * @return array<array-key,mixed>
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
		// Attempt to load the data if it's not found.
		if( empty($this->items[$index]) ){
			$this->load($index);
		}

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
	 * Test if config has given ID.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function has(string $id): bool
	{
		try {

			list($index, $path) = $this->parseKey($id);

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
	 * @param string $id
	 * @throws KeyNotFoundException
	 * @return mixed
	 */
	public function get(string $id)
	{
		list($index, $path) = $this->parseKey($id);

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
	public function set(string $key, mixed $value): void
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
		/** @var LoaderInterface $loader */
		foreach( $this->loaders as $loader ){

			$items = $loader->load($index);

			if( $items !== null ){
				$this->items[$index] = $items;
				break;
			}
		}
	}

	/**
	 * Parse a key into index and path values.
	 *
	 * @param string $key
	 * @throws ConfigException
	 * @return array<string>
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

		throw new ConfigException("Invalid key {$key}.");
	}
}
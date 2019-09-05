<?php

namespace nimbly\Config;


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
		// Does this key/value pair exist in the item store?
        if( $this->has($key) === false ){
			$this->load($key);
        }

		// Try resolving now.
        try {

            $configValue = $this->resolve($key);

        } catch ( ConfigException $exception ){

            return $default;

        }

        return $configValue;
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
	 * Loop through loaders and attempt to load configuration.
	 *
	 * @param string $key
	 * @return void
	 */
	private function load(string $key): void
	{
		foreach( $this->loaders as $loader ){
			if( ($items = \call_user_func([$loader, 'load'], $key)) ){
				$this->items = \array_merge($this->items, $items);
				break;
			}
		}
	}
}
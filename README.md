# Config

A simple configuration manager with lazy loading from files.

## Installation

```bash
composer require nimbly/config
```

## Configuration files

Configuration files should return an associative array with the settings of your choice.

```php
<?php

return [
	"host" => "localhost",
	"port" => 1234,
	"user" => "dbuser",
	"password" => "dbpassword",
];
```

## Usage

Instantiate the ```Config``` manager with the location on disk to your configuration files.

```php
$config = new Config(__DIR__ . "/config");
```

## Accessing values

Configuration keys can be accessed using a dot-notation syntax with the left most being the root file name of the configuration file.

```php
$config->get('database.host');
```

The above would load the contents of the file ```database.php``` from the configuration path passed into the ```Config``` manager.

Your configuration files may contain nested associative arrays that can be accessed using the same dotted notation.

```php
$config->get("database.connections.default.host");
```

## Default values

When getting keys from the config manager, you can optionally define a default value.

```php
$config->get('database.host', 'localhost');
```

## Manually adding values

You may also manually add new key / value pairs into the configuration manager.

```php
$config->add('queue', [
	'name' => 'jobs',
	'host' => 'localhost',
	'port' => 1234
]);
```
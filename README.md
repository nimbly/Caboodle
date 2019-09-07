# Caboodle

A simple configuration manager with lazy loading from files, AWS Secrets Manager, or any other source.

## Installation

```bash
composer require nimbly/config
```

## Usage

Instantiate the ```Config``` manager with an array of LoaderInterface instances. You may specify as many loaders as you"d like - or none at all.

```php
$config = new Config([
	new FileLoader(__DIR__ . "/config")
]);
```

Get data from config.

```php
$config->get("database.hostname");
```

### Loaders

Loaders are responisble for accepting a ```key```, loading, and then passing the data back.

Two loaders are provided out of the box: ```FileLoader``` and ```AwsLoader``` but a ```LoaderInterface``` is provided for implementing any other loader.

#### FileLoader

The ```FileLoader``` will attempt to load configuration data from the local filesystem.

Instantiate the ```FileLoader``` with a path to your configuration files.

```php
new FileLoader("/path/to/config/files");
```

Configuration files should return an associative array of your configuration data.

```php
<?php

// file: config/database.php

return [
	"host" => "localhost",
	"port" => 1234,
	"user" => "dbuser",
	"password" => "dbpassword",
];
```

#### AwsLoader

The ```AwsLoader``` will attempt to load configuration data from AWS Secrets Manager.

```NOTES```
* If your AWS Secrets Manager keys include dots ("."), the loader will not be able to resolve the key name properly. It is suggested that your AWS keys be of the form ```db/default``` as suggested by AWS best practices.
* ```VersionId``` and ```StageVersion``` options are not available with this loader at this time.
* ```SecretBinary``` values are not supported at this time. The loader will only look for values in the ```SecretString``` property.

## Adding loaders dynamically

You can add loaders dynamically.

```php
$config->addLoader(
	new FileLoader(__DIR__ . "/config")
);
```

## Accessing values

Configuration keys can be accessed using a dot-notation syntax with the left most being the ```key``` the loaders will use to resolve and load the configuration data.

```php
$config->get("database.host");
```

The above would load the contents of the file ```database.php``` from the configuration path passed into the ```Config``` manager.

Your configuration files may contain nested associative arrays that can be accessed using the same dotted notation.

```php
$config->get("database.connections.default.host");
```

## Key hinting

By default, the root key name is assumed to be everything before the first dot ("."). However, if the root key name includes a dot, you can hint the key name by using a single hash mark in place of a dot.

For example:

```php
$config->get("prod.database#connections.default.hostname");
```

## Manually adding values

You may also manually add new key / value pairs into the configuration manager.

```php
$config->add("queue", [
	"name" => "jobs",
	"host" => "localhost",
	"port" => 1234
]);
```

Or you may assign the entire contents of the configuration data.

```php
$config->setItems([
	"key1" => "value1",
	"key2" => [
		"key3" => "value3"
	]
]);
```
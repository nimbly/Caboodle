<?php

namespace Nimbly\Caboodle;

use Psr\Container\NotFoundExceptionInterface;

class KeyNotFoundException extends ConfigException implements NotFoundExceptionInterface
{
}
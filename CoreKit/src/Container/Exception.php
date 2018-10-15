<?php

namespace CoreKit\Container;

use Psr\Container\ContainerExceptionInterface;
use Exception as PhpException;

class Exception extends PhpException implements ContainerExceptionInterface
{
}

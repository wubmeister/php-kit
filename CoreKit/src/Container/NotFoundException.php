<?php

namespace CoreKit\Container;

use Psr\Container\NotFoundExceptionInterface;
use Exception as PhpException;

class Exception extends PhpException implements NotFoundExceptionInterface
{
}

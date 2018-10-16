<?php

namespace CoreKit\Container;

use Psr\Container\NotFoundExceptionInterface;
use Exception as PhpException;

class NotFoundException extends PhpException implements NotFoundExceptionInterface
{
}

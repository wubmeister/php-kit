<?php

namespace CoreKit;

use Psr\Container\ContainerInterface;
use CoreKit\Container\Exception as ContainerException;
use CoreKit\Container\NotFoundException;

class Container implements ContainerInterface
{
    protected $entries = [];
    protected $factories = [];

    public function get($id)
    {
        if (!isset($this->entries[$id])) {
            if (!isset($this->factories[$id])) {
                throw new NotFoundException("No entry or factory found for '{$id}'");
            }

            $this->entries[$id] = $this->factories[$id]($this, $id);
        }

        return $this->entries[$id];
    }

    public function has($id)
    {
        return isset($this->entries[$id]) || isset($this->factories[$id]);
    }

    public function set($id, $entry)
    {
        $this->entries[$id] = $entry;
    }

    public function addFactory($id, callable $factory)
    {
        $this->factories[$id] = $factory;
    }
}

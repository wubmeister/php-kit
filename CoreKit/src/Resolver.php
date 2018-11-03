<?php

namespace CoreKit;

class Resolver
{
    protected $paths = [];

    public function addPath($path)
    {
        $this->paths[] = rtrim($path, '/') . '/';
    }

    public function resolve($file)
    {
        $file = ltrim($file, '/');
        foreach ($this->paths as $path) {
            $glob = glob($path.$file, GLOB_BRACE);
            if (count($glob) > 0) {
                return $glob[0];
            }
        }

        return null;
    }
}

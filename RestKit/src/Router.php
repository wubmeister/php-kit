<?php

namespace RestKit;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    protected $basePath;
    protected $resources;
    protected $middlewareClass;
    protected $middlewareArgs;

    public function __construct($config, $basePath = '')
    {
        $this->resources = $config;
        $this->basePath = $basePath;
    }

    public function parseRequest(ServerRequestInterface $request)
    {
        $urlPath = substr($request->getUri()->getPath(), strlen($this->basePath));
        $chunks = explode('/', trim($urlPath, '/'));

        $resources = $this->resources;
        $resource = null;
        $key = null;
        $id = null;
        $ids = [];
        $tail = '';

        foreach ($chunks as $index => $chunk) {
            if (!$key) {
                $key = $chunk;
                if (isset($resources[$key])) {
                    $resource = $key;
                    if (isset($resources[$key]['middleware'])) {
                        $middlewareClass = $resources[$key]['middleware'];
                        $middlewareArgs = isset($resources[$key]['arguments']) ? $resources[$key]['arguments'] : [];
                    } else {
                        $middlewareClass = $middlewareArgs = null;
                    }
                    $resources = $resources[$key]['children'] ?? [];
                } else {
                    $tail = implode('/', array_slice($chunks, $index));
                    break;
                }
            } else {
                $ids["{$resource}_id"] = $chunk;
                $id = $chunk;
            }
        }

        if ($resource) {
            $request = $request
                ->withAttribute('resource', $resource)
                ->withAttribute('id', $id)
                ->withAttribute('tail', $tail);
            foreach ($ids as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
            $this->middlewareClass = $middlewareClass;
            $this->middlewareArgs = $middlewareArgs;
        }

        return $request;
    }

    public function getMiddleware()
    {
        if (!$this->middlewareClass) return null;

        $rc = new \ReflectionClass($this->middlewareClass);
        return $rc->newInstanceArgs($this->middlewareArgs);
    }
}

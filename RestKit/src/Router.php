<?php

namespace RestKit;

use Psr\Http\Message\ServerRequestInterface;

class Router
{
    protected $basePath;
    protected $resources;
    protected $middlewareClass;
    protected $middlewareArgs;
    protected $match;

    public function __construct($config, $basePath = '')
    {
        $this->resources = $config;
        $this->basePath = rtrim($basePath, '/');
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
                    $this->match = $resources[$key];
                    $resources = $resources[$key]['children'] ?? [];
                    if (isset($this->match['acceptsId']) && $this->match['acceptsId'] === false) {
                        $key = null;
                    }
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
        return $this->middlewareClass;
    }

    public function getMatch()
    {
        return $this->match;
    }

    public function build($name, $ids = [])
    {
        if (!is_array($ids)) {
            $ids = [ $ids ];
        }

        $route = [];

        if ($name != '/') {
            $chunks = explode('/', $name);
            $resources = $this->resources;
            foreach ($chunks as $chunk) {
                if (isset($resources[$chunk])) {
                    $route[] = $chunk;
                    if ((!isset($resources[$chunk]['acceptsId']) || $resources[$chunk]['acceptsId'] === true) && count($ids) > 0) {
                        $route[] = array_shift($ids);
                    }
                }
            }
        }

        return $this->basePath . '/' . implode($route);
    }
}

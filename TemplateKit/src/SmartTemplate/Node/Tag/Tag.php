<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

use CoreKit\Resolver;
use TemplateKit\SmartTemplate\Node\Node;

class Tag extends Node
{
    public $isSelfClosing = false;

    protected $attributes = [];

    protected static $systemTags = [
        'include' => IncludeTag::class,
        'translate' => Translate::class,
    ];

    public function __construct(string $name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public static function factory(string $name, array $attributes, Resolver $resolver)
    {
        if (isset(self::$systemTags[$name])) {
            return new self::$systemTags[$name]($name, $attributes);
        } if ($file = $resolver->resolve($name.'.tpl')) {
            return new Subtemplate($name, $file, $attributes);
        } else if ($file = $resolver->resolve($name.'.phtml')) {
            return new PhpTemplate($name, $file, $attributes);
        } else if ($file = $resolver->resolve(ucfirst($name).'.php')) {
            $contents = file_get_contents($file);
            $namespace = '';
            $className = ucfirst($name);
            if (preg_match("/[\r\n]\s*namespace\s+([a-zA-Z0-9_\\\\]+)/m", $contents, $match)) {
                $namespace = $match[1];
            }
            if (preg_match("/[\r\n]\s*class\s+([a-zA-Z0-9_]+)\s+extends\s+([a-zA-Z0-9_\\\\]+)/m", $contents, $match)) {
                $className = $match[1];
                $extends = basename(str_replace('\\', '/', $match[2]));
            }
            if ($namespace) {
                $className = $namespace.'\\'.$className;
            }
            $tag = new Extension($name, $file, $className, $attributes);
            if ($extends == 'InlineFunction') $tag->isSelfClosing = true;

            return $tag;
        }

        return new Tag($name, $attributes);
    }
}

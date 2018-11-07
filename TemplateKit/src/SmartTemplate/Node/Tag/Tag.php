<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

use CoreKit\Resolver;
use TemplateKit\SmartTemplate\Node\Node;
use TemplateKit\SmartTemplate\ExpressionAttribute;

class Tag extends Node
{
    public $isSelfClosing = true;

    protected $attributes = [];

    protected static $systemTags = [
        'capture' => Capture::class,
        'include' => IncludeTag::class,
        'translate' => Translate::class,
    ];

    public function __construct(string $name, array $attributes)
    {
        if (strpos($name, ':') !== false) {
            $pair = explode(':', $name, 2);
            $this->name = $pair[1];
            $this->namespace = $pair[0];
        } else {
            $this->name = $name;
        }
        $this->attributes = $attributes;
    }

    public static function factory(string $name, array $attributes, Resolver $resolver)
    {
        $resName = str_replace(':', '/', $name);
        if (isset(self::$systemTags[$name])) {
            return new self::$systemTags[$name]($name, $attributes);
        } if ($file = $resolver->resolve($resName.'.tpl')) {
            return new Subtemplate($name, $file, $attributes);
        } else if ($file = $resolver->resolve($resName.'.phtml')) {
            return new PhpTemplate($name, $file, $attributes);
        } else if ($file = $resolver->resolve(str_replace(' ', '/', ucwords(str_replace(':', ' ', $name))).'.php')) {
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
            if ($extends == 'Inline') $tag->isSelfClosing = true;
            elseif ($extends == 'CustomBlock') $tag->handleChildren = "manual";

            return $tag;
        }

        return new Tag($name, $attributes);
    }

    protected function getAttributesString()
    {
        $str = '[';
        $index = 0;
        foreach ($this->attributes as $key => $value) {
            if ($index > 0) $str .= ', ';
            $str .= '"' . $key . '" => ';
            if (is_null($value)) $str .= 'null';
            else if (is_bool($value)) $str .= $value ? 'true' : 'false';
            else if (is_numeric($value)) $str .= $value;
            else if (is_string($value)) $str .= '"' . $value . '"';
            else if ($value instanceof ExpressionAttribute) $str .= $value;
            $index++;
        }
        $str .= ']';

        return $str;
    }

    public function dehydrate()
    {
        $dry = parent::dehydrate();
        $dry["attributes"] = $this->attributes;

        return $dry;
    }
}

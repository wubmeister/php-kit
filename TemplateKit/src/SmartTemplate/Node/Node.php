<?php

namespace TemplateKit\SmartTemplate\Node;

class Node
{
    public $name = '';
    public $children = [];

    protected $includes = [];
    protected $parent;

    public function appendChild(Node $child)
    {
        $child->parent = $this;
        $this->children[] = $child;
    }

    public function getPhpCode()
    {
        $php = '';
        foreach ($this->children as $child) {
            $php .= $child->getPhpCode();
        }
        return $php;
    }

    public function getIncludes()
    {
        $includes = $this->includes;
        foreach ($this->children as $child) {
            $includes = array_merge($includes, $child->getIncludes());
        }

        return $includes;
    }
}
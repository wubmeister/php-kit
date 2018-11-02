<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

use TemplateKit\SmartTemplate\Compiler;

class Subtemplate extends Tag
{
    protected $file;

    public function __construct($name, $file, $attributes)
    {
        parent::__construct($name, $attributes);
        $this->file = $file;

        $parser = Compiler::getParser();
        $root = $parser->parse(file_get_contents($file));
        foreach ($root->children as $child) {
            $this->appendChild($child);
        }
        $this->includes = $root->getIncludes();
    }
}
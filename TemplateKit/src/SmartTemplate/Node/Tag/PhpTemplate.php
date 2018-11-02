<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class PhpTemplate extends Tag
{
    protected $file;

    public function __construct($name, $file, $attributes)
    {
        parent::__construct($name, $attributes);
        $this->file = $file;
    }

    public function getPhpCode()
    {
        return file_get_contents($this->file);
    }
}
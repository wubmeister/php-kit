<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class PhpTemplate extends Tag
{
    public $isSelfClosing = true;

    protected $file;

    public function __construct($name, $file, $attributes)
    {
        parent::__construct($name, $attributes);
        $this->file = $file;
    }

    public function getPhpCode()
    {
        $php = '<?php $attr = ' . $this->getAttributesString() . '; ?>' . PHP_EOL;
        $php .= file_get_contents($this->file);
        return $php;
    }
}

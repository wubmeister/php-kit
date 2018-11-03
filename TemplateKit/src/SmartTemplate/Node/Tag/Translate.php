<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

use TemplateKit\SmartTemplate\Compiler;

class Translate extends Tag
{
    public $isSelfClosing = true;

    public function getPhpCode()
    {
        return '<?php echo _(' . $this->attributes['expression'] . '); ?>';
    }
}

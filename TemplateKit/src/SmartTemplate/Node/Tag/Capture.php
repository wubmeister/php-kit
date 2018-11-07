<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class Capture extends Tag
{
    public $isSelfClosing = false;

    public function getPhpCode()
    {
        $name = trim((string)$this->attributes['expression'], '"\'');
        $php = '<?php $this->capture("' . $name . '"); ?>';
        $php .= parent::getPhpCode();
        $php .= '<?php $this->endCapture(); ?>';

        return $php;
    }
}

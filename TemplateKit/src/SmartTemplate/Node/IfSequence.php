<?php

namespace TemplateKit\SmartTemplate\Node;

class IfSequence extends Control
{
    public function __construct()
    {
        parent::__construct('ifsequence');
    }

    public function getPhpCode()
    {
        $php = '';
        foreach ($this->children as $child) {
            $php .= $child->getPhpCode();
        }
        $php .= '<?php endif; ?>';
        return $php;
    }
}
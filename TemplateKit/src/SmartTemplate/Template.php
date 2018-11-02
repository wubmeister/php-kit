<?php

namespace TemplateKit\SmartTemplate;

use TemplateKit\Template;

class SmartTemplate extends Template
{
    public function render()
    {
        $variables = $this->getVariables();
        extract($variables);
        $filename = Compiler::getCompiledFile($this->file);

        ob_start();
        include $filename;
        $contents = ob_get_clean();

        return $contents;
    }
}
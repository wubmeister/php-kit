<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

use TemplateKit\SmartTemplate\Compiler;

class IncludeTag extends Tag
{
    public $isSelfClosing = true;

    public function getPhpCode()
    {
        $file = trim((string)$this->attributes['expression'], '"\'');
        $wd = Compiler::getWorkingDir();

        if (pathinfo($file, PATHINFO_EXTENSION) == 'tpl') {
            $filename = Compiler::getWorkingDir() . $file;
            if (!file_exists($filename)) {
                throw new \Exception("File not found: {$filename}");
            }
            $parser = Compiler::getParser();
            Compiler::setWorkingDir(dirname($filename));
            $inc = $parser->parse(file_get_contents($filename));
            $php = $inc->getPhpCode();
            Compiler::setWorkingDir($wd);
            return $php;
        }

        return '<?php include "' . $wd.$file . '"; ?>';
    }
}

<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class PhpTemplate extends Tag
{
    public $isSelfClosing = true;

    protected $file;
    protected $fileContents;

    public function __construct($name, $file, $attributes)
    {
        parent::__construct($name, $attributes);
        $this->file = $file;
        $this->fileContents = file_get_contents($file);
        if (preg_match('/^<!--\s*Options:/m', $this->fileContents)) {
            $pos = strpos($this->fileContents, '-->');
            if ($pos !== false) {
                $comments = trim(substr($this->fileContents, 4, $pos - 4));
                $comments = trim(substr($comments, 8));
                $options = json_decode($comments, true);
                if (isset($options['selfClosing'])) $this->isSelfClosing = (bool)$options['selfClosing'];
            }
        }
    }

    public function getPhpCode()
    {
        $php = '<?php $attr = ' . $this->getAttributesString() . '; ?>' . PHP_EOL;
        $php .= $this->fileContents;
        return $php;
    }
}

<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class PhpTemplate extends Tag
{
    public $isSelfClosing = true;

    protected $file;
    protected $fileContents;
    protected $handleChildren = 'include';

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
                if (isset($options['handleChildren'])) $this->handleChildren = (bool)$options['handleChildren'];
            }
        }
    }

    public function getPhpCode()
    {
        $php = '<?php $attr = ' . $this->getAttributesString() . '; ?>' . PHP_EOL;
        if (!$this->isSelfClosing) {
            if ($this->handleChildren == 'manual') {
                $dryTags = [];
                foreach ($this->children as $child) {
                    $dryTags[] = $child->dehydrate();
                }
                $php .= '<?php $children = ' . \CoreKit\Serialize::toPhp($dryTags) . '; ?>';
            } else {
                if (count($this->children) == 0) {
                    $php .= '<?php $contents = ""; ?>';
                } else {
                    $php .= '<?php ob_start(); ?>';
                    foreach ($this->children as $child) {
                        $php .= $child->getPhpCode();
                    }
                    $php .= '<?php $contents = ob_get_clean(); ?>';
                }
            }
        }
        $php .= $this->fileContents;
        return $php;
    }
}

<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class Extension extends Tag
{
    protected $className;

    public  $handleChildren = 'include';

    public function __construct($name, $file, $className, $attributes)
    {
        parent::__construct($name, $attributes);
        $this->file = $file;
        $this->className = $className;

        $this->includes[] = $file;
    }

    public function getPhpCode()
    {
        if ($this->isSelfClosing) {
            $php = '<?php echo '.$this->className.'::instance()('.$this->getAttributesString().'); ?>' . PHP_EOL;
        } else {
            if ($this->handleChildren == 'manual') {
                $dryTags = [];
                foreach ($this->children as $child) {
                    $dryTags[] = $child->dehydrate();
                }
                $php .= '<?php $children = ' . \CoreKit\Serialize::toPhp($dryTags) . ';' . PHP_EOL . 'echo '.$this->className.'::instance()($children, '.$this->getAttributesString().'); ?>';
            } else {
                $php = '<?php ob_start(); ?>';
                $php .= parent::getPhpCode();
                $php .= '<?php $_ = ob_get_clean(); echo '.$this->className.'::instance()($_, '.$this->getAttributesString().'); ?>' . PHP_EOL;
            }
        }

        return $php;
    }
}

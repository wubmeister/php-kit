<?php

namespace TemplateKit\SmartTemplate\Node\Tag;

class Extension extends Tag
{
    protected $className;

    public function __construct($name, $file, $className, $attributes)
    {
        parent::__construct($name, $attributes);
        $this->file = $file;
        $this->className = $className;

        $this->includes[] = $file;
    }

    protected function getAttributesString()
    {
        $str = '[';
        $index = 0;
        foreach ($this->attributes as $key => $value) {
            if ($index > 0) $str .= ', ';
            $str .= '"' . $key . '" => ';
            if (is_null($value)) $str .= 'null';
            if (is_bool($value)) $str .= $value ? 'true' : 'false';
            else if (is_numeric($value)) $str .= $value;
            else if (is_string($value)) $str .= $value;
            else if ($value instanceof ExpressionAttribute) $str .= $value->getLiteral();
            $index++;
        }
        $str .= ']';

        return $str;
    }

    public function getPhpCode()
    {
        if ($this->isSelfClosing) {
            $php = '<?php echo '.$this->className.'::instance()('.$this->getAttributesString().'); ?>' . PHP_EOL;
        } else {
            $php = '<?php ob_start(); ?>';
            $php .= parent::getPhpCode();
            $php .= '<?php $_ = ob_get_clean(); echo '.$this->className.'::instance()($_, '.$this->getAttributesString().'); ?>' . PHP_EOL;
        }

        return $php;
    }
}
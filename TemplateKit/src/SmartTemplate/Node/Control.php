<?php

namespace TemplateKit\SmartTemplate\Node;

use CoreKit\Range;
use TemplateKit\SmartTemplate\Expression as ExpressionObj;

class Control extends Node
{
    public $expression;

    public function __construct(string $name, ExpressionObj $expression = null)
    {
        $this->name = $name;
        $this->expression = $expression;
    }

    public function getPhpCode()
    {
        if ($this->name == 'for') {
            if (!preg_match('/^(\$[a-zA-Z_][a-zA-Z0-9_]*)(,\s*\$[a-zA-Z_][a-zA-Z0-9_]*)?\s+in\s+(.+)/m', $this->expression, $match)) {
                throw new Exception('Invalid FOR statement; should be \'for $iterator in $collection\'');
            }
            $iterator = $match[2] ? ltrim(substr($match[2], 1)) : $match[1];
            $key = $match[2] ? $match[1] : null;
            $expression = $match[3];
            if (strpos($expression, '...') !== false) {
                $pair = explode('...', $expression, 2);
                $expression = 'Range::incl(' . implode(',', $pair) . ')';
            } else if (strpos($expression, '..<') !== false) {
                $pair = explode('..<', $expression, 2);
                $expression = 'Range::excl(' . implode(',', $pair) . ')';
            }
            $php = '<?php foreach (' . $expression . ' as ' . ($key ? $key . ' => ' : '') . $iterator . '): ?>';
            $php .= parent::getPhpCode();
            $php .= '<?php endforeach; ?>';
        } else {
            $expression = $this->expression ? (string)$this->expression->phpized() : null;
            $php = '<?php ' . $this->name . ($expression ? ' (' . $expression . ')' : '') . ': ?>';
            $php .= parent::getPhpCode();
            if (!in_array($this->name, [ 'else', 'elseif', 'if' ])) {
                $php = '<?php end' . $this->name . '; ?>';
            }
        }

        return $php;
    }
}
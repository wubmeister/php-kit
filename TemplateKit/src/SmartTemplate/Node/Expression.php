<?php

namespace TemplateKit\SmartTemplate\Node;

use TemplateKit\SmartTemplate\Expression as ExpressionObj;

class Expression extends Content
{
    public function __construct(ExpressionObj $content)
    {
        $this->content = strtr($content, [ '<?' => '', '?>' => '' ]);
    }

    public function getPhpCode()
    {
        return '<?php echo ' . $this->content . '; ?>';
    }
}
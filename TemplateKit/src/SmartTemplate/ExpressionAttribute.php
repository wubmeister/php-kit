<?php

namespace TemplateKit\SmartTemplate;

class ExpressionAttribute
{
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getLiteral()
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->getLiteral();
    }
}
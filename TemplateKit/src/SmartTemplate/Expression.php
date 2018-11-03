<?php

namespace TemplateKit\SmartTemplate;

class Expression
{
    protected $parts = [];

    public function append($part)
    {
        $this->parts[] = [ 'type' => 'part', 'content' => $part ];
    }

    public function appendString($string)
    {
        $this->parts[] = [ 'type' => 'string', 'content' => $string ];
    }

    public function phpized()
    {
        $newExp = new Expression();
        foreach ($this->parts as $part) {
            if ($part['type'] == 'part') {
                $part['content'] = preg_replace_callback('/\s(and|or)\s/i', function($match) {
                    return $match[1] == 'and' ? ' && ' : ' || ';
                }, $part['content']);
            }
            $newExp->parts[] = $part;
        }

        return $newExp;
    }

    public function __toString()
    {
        return implode('', array_map(function($part){return $part['content'];}, $this->parts));
    }
}

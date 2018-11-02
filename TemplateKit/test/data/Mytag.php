<?php

class Mytag extends BlockFunction
{
    public function __invoke($content, $attributes)
    {
        return '(' . implode(', ', $attributes) . ') ' . strtoupper($content);
    }
}

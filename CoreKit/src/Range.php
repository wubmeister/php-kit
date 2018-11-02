<?php

namespace CoreKit;

class Range implements Iterator
{
    protected $start;
    protected $end;
    protected $index;
    protected $curr;

    public static function incl($start, $end) {
        return new Range($start, $end);
    }

    public static function excl($start, $end) {
        return new Range($start, $end - 1);
    }

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function rewind()
    {
        $this->curr = $this->start;
        $this->index = 0;
    }

    public function next()
    {
        $this->curr++;
        $this->index++;
    }

    public function valid()
    {
        return $this->curr <= $this->end;
    }

    public function current()
    {
        return $this->curr;
    }

    public function key()
    {
        return $this->index;
    }
}
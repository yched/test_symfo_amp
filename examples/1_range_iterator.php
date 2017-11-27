<?php

class RangeIterator implements Iterator
{
    protected $start;
    protected $end;
    protected $step;
    protected $index;

    public function __construct($start, $end, $step = 1)
    {
        $this->start = $start;
        $this->end = $end;
        $this->step = $step;
        $this->index = 0;
    }

    public function current()
    {
        return $this->start + ($this->index * $this->step);
    }

    public function next()
    {
        $this->index++;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return $this->current() <= $this->end;
    }

    public function rewind()
    {
        $this->index = 0;
    }
}

$range = new RangeIterator(5, 10, 2);
foreach ($range as $key => $value) {
    echo "$key => $value" . PHP_EOL;
}

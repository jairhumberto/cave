<?php

namespace Squille\Cave;

use Iterator;

abstract class ArrayList implements Iterator, ListInterface
{
    private $index;
    private $items;

    public function __construct($items = [])
    {
        $this->index = 0;
        $this->items = $items;
    }

    public function any()
    {
        return count($this->items) > 0;
    }

    public function count()
    {
        return count($this->items);
    }

    public function get($index)
    {
        return $this->items[$index];
    }

    public function search($condition)
    {
        foreach ($this as $item) {
            if ($condition($item)) {
                return $item;
            }
        }
        return null;
    }

    public function current()
    {
        return $this->items[$this->index];
    }

    public function next()
    {
        ++$this->index;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return isset($this->items[$this->index]);
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function merge($list)
    {
        foreach ($list as $item) {
            $this->add($item);
        }
        return $this;
    }

    public function add($item)
    {
        $this->items[] = $item;
    }

    public function __toString()
    {
        $strings = [];
        foreach ($this as $item) {
            $strings[] = $item;
        }
        return join(",", $strings);
    }
}

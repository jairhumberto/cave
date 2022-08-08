<?php

namespace Squille\Cave;

use Iterator;

abstract class ArrayList implements Iterator, IList
{
    private $index;
    private $items;

    public function __construct($items = [])
    {
        $this->index = 0;
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function any()
    {
        return count($this->items) > 0;
    }

    /**
     * @inheritDoc
     */
    public function get($index)
    {
        return $this->items[$index];
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function merge($list)
    {
        foreach ($list as $item) {
            $this->add($item);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
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

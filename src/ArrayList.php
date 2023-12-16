<?php

namespace Squille\Cave;

abstract class ArrayList implements ListInterface
{
    private $index;
    private $items;

    public function __construct($items = [])
    {
        $this->index = 0;
        $this->items = $items;
    }

    public function any(): bool
    {
        return count($this->items) > 0;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function get(int $index)
    {
        return $this->items[$index];
    }

    public function search(callable $condition)
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

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->index]);
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function merge(ListInterface $list): ArrayList
    {
        foreach ($list as $item) {
            $this->add($item);
        }
        return $this;
    }

    public function add($item): void
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

<?php

namespace Squille\Cave;

use Iterator;

interface ListInterface extends Iterator
{
    /**
     * @param mixed $item
     */
    public function add($item);

    public function any(): bool;
    public function count(): int;

    /**
     * @param int $index
     * @return mixed
     */
    public function get(int $index);

    public function merge(ListInterface $list): ListInterface;

    /**
     * @param callable $condition
     * @return mixed
     */
    public function search(callable $condition);
}

<?php

namespace Squille\Cave;

use Iterator;

interface ListInterface extends Iterator
{
    /**
     * @param mixed $item
     */
    public function add($item);

    /**
     * @return bool
     */
    public function any();

    /**
     * @return int
     */
    public function count();

    /**
     * @param int $index
     * @return mixed
     */
    public function get($index);

    /**
     * @param ListInterface $list
     */
    public function merge($list);

    /**
     * @param callable $condition
     * @return mixed
     */
    public function search($condition);
}

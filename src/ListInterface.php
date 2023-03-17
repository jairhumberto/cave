<?php

namespace Squille\Cave;

use Iterator;

interface ListInterface
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
     * @param Iterator $list
     */
    public function merge($list);

    /**
     * @param callable $condition
     * @return mixed
     */
    public function search($condition);
}

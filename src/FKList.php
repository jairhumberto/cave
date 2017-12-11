<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class FKList
{
    protected $itens;

    public function __construct()
    {
        $this->itens = array();
    }

    public function length()
    {
        return count($this->itens);
    }

    public function item($index)
    {
        return $this->itens[$index];
    }

    public function addItem(FK $item)
    {
        $this->itens[] = $item;
    }

    public function getItens()
    {
        return $this->itens;
    }
}

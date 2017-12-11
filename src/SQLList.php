<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class SQLList
{
    protected $itens;

    public function __construct()
    {
        $this->itens = array();
    }

    public function addItem(SQL $sql)
    {
        $this->itens[] = $sql;
    }

    public function getItens()
    {
        return $this->itens;
    }
}

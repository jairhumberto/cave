<?php\r\n/**\r\n * Squille Cave (https://github.com/jairhumberto/Cave\r\n * \r\n * @copyright Copyright (c) 2018 Squille\r\n * @license   this software is distributed under MIT license, see the\r\n *            LICENSE file.\r\n */\r\n\r\n
namespace Squille\Cave;

class SQLList {
    protected $itens;

    public function __construct() {
        $this->itens = array();
    }

    public function addItem(SQL $sql) {
        $this->itens[] = $sql;
    }

    public function getItens() {
        return $this->itens;
    }
}

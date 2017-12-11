<?php
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

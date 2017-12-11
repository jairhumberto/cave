<?php
class FKList {

    protected $itens;

    public function __construct() {
        $this->itens = array();
    }

    public function length() {
        return count($this->itens);
    }

    public function item($index) {
        return $this->itens[$index];
    }

    public function addItem(FK $item) {
        $this->itens[] = $item;
    }

    public function getItens() {
        return $this->itens;
    }

}
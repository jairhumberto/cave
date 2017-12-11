<?php
class IndexList {
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

    public function addItem(Index $item) {
        $this->itens[] = $item;
    }

    public function getItens() {
        return $this->itens;
    }

    public function join($separator) {
        foreach($this->itens as $index) {
            $itens[] = $index->getColumn_name();
        }
        return implode($separator, $itens);
    }
}
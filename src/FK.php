<?php
class FK {

    protected $symbol;

    protected $indexes;
    protected $references;

    public function __construct() {
        $this->indexes = new IndexList;
        $this->references = new ReferenceList;
    }

    public function getSymbol() {
        return $this->symbol;
    }

    public function setSymbol($value) {
        $this->symbol = $value;
    }

    public function getIndexes () {
        return $this->indexes;
    }

    public function getReferences () {
        return $this->references;
    }

}
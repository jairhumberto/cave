<?php
class ReferenceList extends IndexList {
    protected $table;
    public function getTable() {
        return $this->table;
    }
    public function setTable($value) {
        $this->table = $value;
    }
}
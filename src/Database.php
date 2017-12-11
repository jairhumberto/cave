<?php
class Database {
    protected $charset;
    protected $collation;

    protected $tables;

    public function __construct() {
        $this->tables = new TableList;
    }

    public function getCharset() {
        return $this->charset;
    }

    public function setCharset($charset) {
        $this->charset = $charset;
    }

    public function getCollation() {
        return $this->collation;
    }

    public function setCollation($collation) {
        $this->collation = $collation;
    }

    public function getTables() {
        return $this->tables;
    }
}
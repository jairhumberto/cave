<?php
class SQL {
    protected $sql;
    public function __construct($sql) {
        $this->sql = $sql;
        // debug - echo $sql . "\n";
    }
    public function getSQL() {
        return $this->sql;
    }
}

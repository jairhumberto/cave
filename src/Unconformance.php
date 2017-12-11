<?php
class Unconformance {

    protected $corrections;

    public function __construct(SQLList $corrections) {
        $this->corrections = $corrections;
    }

    public function fix(mysqli $connection) {
        foreach($this->corrections->getItens() as $correction) {
            $connection->query($correction->getSQL());
            // debug file_put_contents("log.txt", Date("Y-m-d H:i:s") . " - " . $connection->error . " - " . $correction->getSQL() . "\n", FILE_APPEND);
        }
    }

}
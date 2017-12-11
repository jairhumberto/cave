<?php\r\n/**\r\n * Squille Cave (https://github.com/jairhumberto/Cave\r\n * \r\n * @copyright Copyright (c) 2018 Squille\r\n * @license   this software is distributed under MIT license, see the\r\n *            LICENSE file.\r\n */\r\n\r\n
namespace Squille\Cave;

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

<?php\r\n/**\r\n * Squille Cave (https://github.com/jairhumberto/Cave\r\n * \r\n * @copyright Copyright (c) 2018 Squille\r\n * @license   this software is distributed under MIT license, see the\r\n *            LICENSE file.\r\n */\r\n\r\n
namespace Squille\Cave;

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
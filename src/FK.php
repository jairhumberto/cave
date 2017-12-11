<?php\r\n/**\r\n * Squille Cave (https://github.com/jairhumberto/Cave\r\n * \r\n * @copyright Copyright (c) 2018 Squille\r\n * @license   this software is distributed under MIT license, see the\r\n *            LICENSE file.\r\n */\r\n\r\n
namespace Squille\Cave;

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
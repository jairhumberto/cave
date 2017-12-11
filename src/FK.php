<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class FK
{
    protected $symbol;

    protected $indexes;
    protected $references;

    public function __construct()
    {
        $this->indexes = new IndexList;
        $this->references = new ReferenceList;
    }

    public function getSymbol()
    {
        return $this->symbol;
    }

    public function setSymbol($value)
    {
        $this->symbol = $value;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function getReferences()
    {
        return $this->references;
    }
}

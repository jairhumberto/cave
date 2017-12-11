<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class Table
{
    protected $Name;
    protected $Engine;
    protected $Row_format;
    protected $Charset;
    protected $Collation;
    protected $Checksum;

    protected $fields;
    protected $indexes;
    protected $fks;

    public function __construct()
    {
        $this->fields = new FieldList;
        $this->indexes = new IndexList;
        $this->fks = new FKList;
    }

    public function getName()
    {
        return $this->Name;
    }

    public function setName($value)
    {
        $this->Name = $value;
    }

    public function getEngine()
    {
        return $this->Engine;
    }

    public function setEngine($value)
    {
        $this->Engine = $value;
    }

    public function getRow_format()
    {
        return $this->Row_format;
    }

    public function setRow_format($value)
    {
        $this->Row_format = $value;
    }

    public function getCharset()
    {
        return $this->Charset;
    }

    public function setCharset($value)
    {
        $this->Charset = $value;
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    public function setCollation($value)
    {
        $this->Collation = $value;
    }

    public function getChecksum()
    {
        return $this->Checksum;
    }

    public function setChecksum($value)
    {
        $this->Checksum = $value;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function getFKs()
    {
        return $this->fks;
    }
}

<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 *
 * MIT License
 *
 * Copyright (c) 2017 Jair Humberto
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
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

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

namespace Squille\Cave\V1;

class Index
{
    protected $Non_unique;
    protected $Key_name;
    protected $Seq_in_index;
    protected $Column_name;
    protected $Collation;
    protected $Sub_part;
    protected $Packed;
    protected $Null;
    protected $Index_type;
    protected $Comment;

    public function getNon_unique()
    {
        return $this->Non_unique;
    }

    public function setNon_unique($value)
    {
        $this->Non_unique = $value;
    }

    public function getKey_name()
    {
        return $this->Key_name;
    }

    public function setKey_name($value)
    {
        $this->Key_name = $value;
    }

    public function getSeq_in_index()
    {
        return $this->Seq_in_index;
    }

    public function setSeq_in_index($value)
    {
        $this->Seq_in_index = $value;
    }

    public function getColumn_name()
    {
        return $this->Column_name;
    }

    public function setColumn_name($value)
    {
        $this->Column_name = $value;
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    public function setCollation($value)
    {
        $this->Collation = $value;
    }

    public function getSub_part()
    {
        return $this->Sub_part;
    }

    public function setSub_part($value)
    {
        $this->Sub_part = $value;
    }

    public function getPacked()
    {
        return $this->Packed;
    }

    public function setPacked($value)
    {
        $this->Packed = $value;
    }

    public function getNull()
    {
        return $this->Null;
    }

    public function setNull($value)
    {
        $this->Null = $value;
    }

    public function getIndex_type()
    {
        return $this->Index_type;
    }

    public function setIndex_type($value)
    {
        $this->Index_type = $value;
    }

    public function setComment($value)
    {
        $this->Comment = $value;
    }

    public function getComment()
    {
        return $this->Comment;
    }
}

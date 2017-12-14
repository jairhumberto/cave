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

class Field
{
    protected $Field;
    protected $Type;
    protected $Charset;
    protected $Collation;
    protected $Null;
    protected $Key;
    protected $Default;
    protected $Extra;
    protected $Comment;

    public function getField()
    {
        return $this->Field;
    }

    public function setField($value)
    {
        $this->Field = $value;
    }

    public function getType()
    {
        return $this->Type;
    }

    public function setType($value)
    {
        $this->Type = $value;
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

    public function getNull()
    {
        return $this->Null;
    }

    public function setNull($value)
    {
        $this->Null = $value;
    }

    public function getKey()
    {
        return $this->Key;
    }

    public function setKey($value)
    {
        $this->Key = $value;
    }

    public function getDefault()
    {
        return $this->Default;
    }

    public function setDefault($value)
    {
        $this->Default = $value;
    }

    public function getExtra()
    {
        return $this->Extra;
    }

    public function setExtra($value)
    {
        $this->Extra = $value;
    }

    public function getComment()
    {
        return $this->Comment;
    }

    public function setComment($value)
    {
        $this->Comment = $value;
    }
}

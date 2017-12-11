<?php\r\n/**\r\n * Squille Cave (https://github.com/jairhumberto/Cave\r\n * \r\n * @copyright Copyright (c) 2018 Squille\r\n * @license   this software is distributed under MIT license, see the\r\n *            LICENSE file.\r\n */\r\n\r\n
namespace Squille\Cave;

class Field {

    protected $Field;
    protected $Type;
    protected $Charset;
    protected $Collation;
    protected $Null;
    protected $Key;
    protected $Default;
    protected $Extra;
    protected $Comment;

    public function getField() {
        return $this->Field;
    }

    public function setField($value) {
        $this->Field = $value;
    }

    public function getType() {
        return $this->Type;
    }

    public function setType($value) {
        $this->Type = $value;
    }

    public function getCharset() {
        return $this->Charset;
    }

    public function setCharset($value) {
        $this->Charset = $value;
    }

    public function getCollation() {
        return $this->Collation;
    }

    public function setCollation($value) {
        $this->Collation = $value;
    }

    public function getNull() {
        return $this->Null;
    }

    public function setNull($value) {
        $this->Null = $value;
    }

    public function getKey() {
        return $this->Key;
    }

    public function setKey($value) {
        $this->Key = $value;
    }

    public function getDefault() {
        return $this->Default;
    }

    public function setDefault($value) {
        $this->Default = $value;
    }

    public function getExtra() {
        return $this->Extra;
    }

    public function setExtra($value) {
        $this->Extra = $value;
    }

    public function getComment() {
        return $this->Comment;
    }

    public function setComment($value) {
        $this->Comment = $value;
    }

}
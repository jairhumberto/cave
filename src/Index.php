<?php\r\n/**\r\n * Squille Cave (https://github.com/jairhumberto/Cave\r\n * \r\n * @copyright Copyright (c) 2018 Squille\r\n * @license   this software is distributed under MIT license, see the\r\n *            LICENSE file.\r\n */\r\n\r\n
namespace Squille\Cave;

class Index {

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

    public function getNon_unique() {
        return $this->Non_unique;
    }

    public function setNon_unique($value) {
        $this->Non_unique = $value;
    }

    public function getKey_name() {
        return $this->Key_name;
    }

    public function setKey_name($value) {
        $this->Key_name = $value;
    }

    public function getSeq_in_index() {
        return $this->Seq_in_index;
    }

    public function setSeq_in_index($value) {
        $this->Seq_in_index = $value;
    }

    public function getColumn_name() {
        return $this->Column_name;
    }

    public function setColumn_name($value) {
        $this->Column_name = $value;
    }

    public function getCollation() {
        return $this->Collation;
    }

    public function setCollation($value) {
        $this->Collation = $value;
    }

    public function getSub_part() {
        return $this->Sub_part;
    }

    public function setSub_part($value) {
        $this->Sub_part = $value;
    }

    public function getPacked() {
        return $this->Packed;
    }

    public function setPacked($value) {
        $this->Packed = $value;
    }

    public function getNull() {
        return $this->Null;
    }

    public function setNull($value) {
        $this->Null = $value;
    }

    public function getIndex_type() {
        return $this->Index_type;
    }

    public function setIndex_type($value) {
        $this->Index_type = $value;
    }

    public function setComment($value) {
        $this->Comment = $value;
    }

    public function getComment() {
        return $this->Comment;
    }

}
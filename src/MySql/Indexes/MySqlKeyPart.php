<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\Models\IKeyPartModel;
use Squille\Cave\UnconformitiesList;

class MySqlKeyPart implements IKeyPartModel
{
    private $pdo;
    private $Non_unique;
    private $Key_name;
    private $Seq_in_index;
    private $Column_name;
    private $Collation;
    private $Sub_part;
    private $Packed;
    private $Null;
    private $Index_type;
    private $Comment;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getNonUnique()
    {
        return $this->Non_unique;
    }

    public function getKeyName()
    {
        return $this->Key_name;
    }

    public function getSeqInIndex()
    {
        return $this->Seq_in_index;
    }

    public function getColumnName()
    {
        return $this->Column_name;
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    public function getSubPart()
    {
        return $this->Sub_part;
    }

    public function getPacked()
    {
        return $this->Packed;
    }

    public function getNull()
    {
        return $this->Null;
    }

    public function getIndexType()
    {
        return $this->Index_type;
    }

    public function getComment()
    {
        return $this->Comment;
    }

    public function checkIntegrity(IKeyPartModel $keyPartModel)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return "{$this->getColumnName()}";
    }
}

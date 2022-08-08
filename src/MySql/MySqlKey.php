<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\Models\IKeyModel;
use Squille\Cave\UnconformitiesList;

class MySqlKey implements IKeyModel
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

    /**
     * @inheritDoc
     */
    public function getNonUnique()
    {
        return $this->Non_unique;
    }

    /**
     * @inheritDoc
     */
    public function getKeyName()
    {
        return $this->Key_name;
    }

    /**
     * @inheritDoc
     */
    public function getSeqInIndex()
    {
        return $this->Seq_in_index;
    }

    /**
     * @inheritDoc
     */
    public function getColumnName()
    {
        return $this->Column_name;
    }

    /**
     * @inheritDoc
     */
    public function getCollation()
    {
        return $this->Collation;
    }

    /**
     * @inheritDoc
     */
    public function getSubPart()
    {
        return $this->Sub_part;
    }

    /**
     * @inheritDoc
     */
    public function getPacked()
    {
        return $this->Packed;
    }

    /**
     * @inheritDoc
     */
    public function getNull()
    {
        return $this->Null;
    }

    /**
     * @inheritDoc
     */
    public function getIndexType()
    {
        return $this->Index_type;
    }

    /**
     * @inheritDoc
     */
    public function getComment()
    {
        return $this->Comment;
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IKeyModel $model)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        $keyParts = $this->getColumnName();
        return "PRIMARY KEY ($keyParts)";
    }
}

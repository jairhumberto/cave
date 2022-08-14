<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\Models\IIndexModel;
use Squille\Cave\UnconformitiesList;

class MySqlPrimaryKey extends AbstractMySqlIndex
{
    private $pdo;

    public function __construct(PDO $pdo, array $keyParts)
    {
        $this->pdo = $pdo;
        parent::__construct($keyParts);
    }

    public function checkIntegrity(IIndexModel $indexModel)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return sprintf("PRIMARY KEY (%s)", parent::__toString());
    }
}

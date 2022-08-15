<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\UnconformitiesList;

class MySqlPrimaryKey extends AbstractMySqlConstraint
{
    private $pdo;

    public function __construct(PDO $pdo, array $keyParts)
    {
        $this->pdo = $pdo;
        parent::__construct($keyParts);
    }

    public function checkIntegrity(IConstraintModel $constraintModel)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return sprintf("PRIMARY KEY (%s)", parent::__toString());
    }
}

<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\UnconformitiesList;

class MySqlPrimaryKey extends AbstractMySqlConstraint
{
    private $pdo;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->pdo = $pdo;
        parent::__construct($partialConstraints);
    }

    public function checkIntegrity(IConstraintModel $constraintModel)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return sprintf("PRIMARY KEY (%s)", parent::__toString());
    }

    public function getName()
    {
        return "PRIMARY";
    }

    public function dropCommand()
    {
        return "DROP PRIMARY KEY";
    }
}

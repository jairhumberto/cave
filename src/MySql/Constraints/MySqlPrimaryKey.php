<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlPrimaryKey extends AbstractMySqlConstraint
{
    public function __construct(PDO $pdo, array $partialConstraints)
    {
        parent::__construct($pdo, $partialConstraints);
    }

    public function getName()
    {
        return "PRIMARY";
    }

    public function dropCommand()
    {
        return "DROP PRIMARY KEY";
    }

    public function __toString()
    {
        return sprintf("PRIMARY KEY (%s)", parent::__toString());
    }
}

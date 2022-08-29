<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;

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

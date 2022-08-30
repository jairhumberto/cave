<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;

class MySqlPrimaryKey extends AbstractMySqlConstraint
{
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

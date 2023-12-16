<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;

class MySqlConstraintFactory
{
    public static function createInstance(PDO $pdo, array $partialConstraints): AbstractMySqlConstraint
    {
        $firstKeyPart = $partialConstraints[0];
        if ($firstKeyPart->getName() == "PRIMARY") {
            return new MySqlPrimaryKey($pdo, $partialConstraints);
        }
        return new MySqlUniqueKey($pdo, $partialConstraints);
    }
}

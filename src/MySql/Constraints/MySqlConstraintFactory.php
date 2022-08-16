<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;

class MySqlConstraintFactory
{
    /**
     * @param PDO $pdo
     * @param array $partialConstraints
     * @return AbstractMySqlConstraint
     */
    public static function createInstance(PDO $pdo, array $partialConstraints)
    {
        $firstKeyPart = $partialConstraints[0];

        if ($firstKeyPart->getConstraintName() == "PRIMARY") {
            return new MySqlPrimaryKey($pdo, $partialConstraints);
        }

        return new MySqlUniqueKey($pdo, $partialConstraints);
    }
}

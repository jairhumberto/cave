<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;

class MySqlConstraintFactory
{
    /**
     * @param PDO $pdo
     * @param array $keyParts
     * @return AbstractMySqlConstraint
     */
    public static function createInstance(PDO $pdo, array $keyParts)
    {
        $firstKeyPart = $keyParts[0];

        if ($firstKeyPart->getKeyName() == "PRIMARY") {
            return new MySqlPrimaryKey($pdo, $keyParts);
        }

        return new MySqlUniqueKey($pdo, $keyParts);
    }
}

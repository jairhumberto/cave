<?php

namespace Squille\Cave\MySql;

use PDO;

class MySqlKeyFactory
{
    /**
     * @param PDO $pdo
     * @param array $keyParts
     * @param string $keyType
     * @return AbstractMySqlKey
     */
    public static function createInstance(PDO $pdo, array $keyParts, $keyType)
    {
        if ($keyType == AbstractMySqlKey::PRIMARY_KEY) {
            return new MySqlPrimaryKey($pdo, $keyParts);
        }

        return new MySqlUniqueKey($pdo, $keyParts);
    }
}

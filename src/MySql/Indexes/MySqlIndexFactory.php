<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;

class MySqlIndexFactory
{
    /**
     * @param PDO $pdo
     * @param array $keyParts
     * @return AbstractMySqlIndex
     */
    public static function createInstance(PDO $pdo, array $keyParts)
    {
        $firstKeyPart = $keyParts[0];

        if ($firstKeyPart->getKeyName() == "PRIMARY") {
            return new MySqlPrimaryKey($pdo, $keyParts);
        }

        if ($firstKeyPart->getIndexType() == "FULLTEXT") {
            return new MySqlFullTextIndex($pdo, $keyParts);
        }

        if ($firstKeyPart->getNonUnique() == 1) {
            return new MySqlIndex($pdo, $keyParts);
        }

        return new MySqlUniqueIndex($pdo, $keyParts);
    }
}

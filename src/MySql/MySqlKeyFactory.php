<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\Models\IKeyPartModel;

class MySqlKeyFactory
{
    /**
     * @param PDO $pdo
     * @param array $keyParts
     * @param string $keyType
     * @return AbstractMySqlKey
     */
    public static function createInstance(PDO $pdo, array $keyParts)
    {
        /** @var IKeyPartModel $firstKeyPart */
        $firstKeyPart = $keyParts[0];

        if ($firstKeyPart->getKeyName() == "PRIMARY") {
            return new MySqlPrimaryKey($pdo, $keyParts);
        }

        if ($firstKeyPart->getNonUnique() == 1) {
            return new MySqlKey($pdo, $keyParts);
        }

        return new MySqlUniqueKey($pdo, $keyParts);
    }
}

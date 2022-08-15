<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\MySql\Constraints\MySqlPrimaryKey;
use Squille\Cave\MySql\Constraints\MySqlUniqueKey;

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

        if ($firstKeyPart->getIndexType() == "FULLTEXT") {
            return new MySqlFullTextIndex($pdo, $keyParts);
        }

        return new MySqlIndex($pdo, $keyParts);
    }
}

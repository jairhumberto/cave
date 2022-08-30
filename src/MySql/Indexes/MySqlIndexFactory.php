<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;

class MySqlIndexFactory
{
    /**
     * @param PDO $pdo
     * @param array $partialIndexes
     * @return AbstractMySqlIndex
     */
    public static function createInstance(PDO $pdo, array $partialIndexes)
    {
        $firstKeyPart = $partialIndexes[0];
        if ($firstKeyPart->getType() == "FULLTEXT") {
            return new MySqlFullTextIndex($pdo, $partialIndexes);
        }
        return new MySqlIndex($pdo, $partialIndexes);
    }
}

<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;

class MySqlIndexFactory
{
    public static function createInstance(PDO $pdo, array $partialIndexes): AbstractMySqlIndex
    {
        $firstKeyPart = $partialIndexes[0];
        if ($firstKeyPart->getType() == "FULLTEXT") {
            return new MySqlFullTextIndex($pdo, $partialIndexes);
        }
        return new MySqlIndex($pdo, $partialIndexes);
    }
}

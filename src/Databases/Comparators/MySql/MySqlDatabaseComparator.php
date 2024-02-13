<?php

declare(strict_types=1);

namespace Squille\Databases\Comparators\MySql;

use PDO;
use Squille\Databases\Comparators\AbstractDatabaseComparator;
use Squille\Databases\Models\Database;
use Squille\Instruction;

class MySqlDatabaseComparator extends AbstractDatabaseComparator
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function getCollationChangeInstruction(Database $model): Instruction
    {
        $query = "ALTER DATABASE COLLATE `{$model->getCollation()}`";
        return new Instruction($query, fn () => $this->pdo->query($query));
    }
}

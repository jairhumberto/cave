<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IIndexesListModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;

class MySqlIndexesList extends ArrayList implements IIndexesListModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, MySqlTable $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        parent::__construct($this->retrieveIndexes());
    }

    private function retrieveIndexes()
    {
        try {
            $selectExpressions = MySqlPartialIndex::selectExpressions() ?: "*";
            $stm = $this->pdo->query("
                SELECT $selectExpressions
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE
                    TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME='{$this->getTable()}'
                    AND NOT EXISTS(
                        SELECT 1
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE
                            TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = '{$this->getTable()}'
                            AND CONSTRAINT_NAME = INDEX_NAME
                        LIMIT 1
                    )
            ");
            return $this->groupIndexes($stm->fetchAll(PDO::FETCH_CLASS, MySqlPartialIndex::class, [$this->pdo]) ?: []);
        } finally {
            if (isset($stm) && $stm instanceof PDOStatement) {
                $stm->closeCursor();
            }
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    private function groupIndexes(array $partialIndexes)
    {
        $keys = [];
        $groups = $this->groupPartialIndexes($partialIndexes);
        foreach ($groups as $group) {
            $keys[] = MySqlIndexFactory::createInstance($this->pdo, $group);
        }
        return $keys;
    }

    private function groupPartialIndexes(array $partialIndexes)
    {
        $groups = [];
        foreach ($partialIndexes as $part) {
            if (!array_key_exists($part->getName(), $groups)) {
                $groups[$part->getName()] = [];
            }
            $groups[$part->getName()][] = $part;
        }
        return $groups;
    }

    public function checkIntegrity(IIndexesListModel $indexesListModel)
    {
        return $this->missingIndexesUnconformities($indexesListModel)
            ->merge($this->generalIndexesUnconformities($indexesListModel));
    }

    public function missingIndexesUnconformities(IIndexesListModel $indexesListModel)
    {
        return new UnconformitiesList();
    }

    public function generalIndexesUnconformities(IIndexesListModel $indexesListModel)
    {
        return new UnconformitiesList();
    }
}

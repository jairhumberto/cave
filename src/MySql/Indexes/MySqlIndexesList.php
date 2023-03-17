<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractIndexesListModel;
use Squille\Cave\Models\AbstractIndexModel;
use Squille\Cave\Models\IndexModelInterface;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\Unconformity;

class MySqlIndexesList extends AbstractIndexesListModel
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
            return $this->groupIndexes($stm->fetchAll(PDO::FETCH_CLASS, MySqlPartialIndex::class, [$this->pdo, $this->table]) ?: []);
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

    protected function missingIndexUnconformity(IndexModelInterface $indexModel)
    {
        $description = "alter table {$this->getTable()} add {$indexModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($indexModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $indexModel");
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingIndexUnconformity(AbstractIndexModel $mySqlIndex)
    {
        $description = "alter table {$this->getTable()} drop index {$mySqlIndex->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlIndex) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} DROP INDEX {$mySqlIndex->getName()}");
        });
        return new Unconformity($description, $instructions);
    }
}

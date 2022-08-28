<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IIndexesListModel;
use Squille\Cave\Models\IIndexModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

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

    public function checkIntegrity(IIndexesListModel $indexesListModel)
    {
        return $this->missingIndexesUnconformities($indexesListModel)
            ->merge($this->generalIndexesUnconformities($indexesListModel));
    }

    private function missingIndexesUnconformities(IIndexesListModel $indexesListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($indexesListModel as $indexModel) {
            $callback = function ($item) use ($indexModel) {
                return $item->getName() == $indexModel->getName();
            };

            $indexFound = $this->search($callback);

            if ($indexFound == null) {
                $unconformities->add($this->missingIndexUnconformity($indexModel));
            }
        }
        return $unconformities;
    }

    private function missingIndexUnconformity(IIndexModel $indexModel)
    {
        $description = "alter table {$this->getTable()} add {$indexModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($indexModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $indexModel");
        });
        return new Unconformity($description, $instructions);
    }

    private function generalIndexesUnconformities(IIndexesListModel $indexesListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $index) {
            $callback = function ($item) use ($index) {
                return $item->getName() == $index->getName();
            };

            $indexModelFound = $indexesListModel->search($callback);

            if ($indexModelFound == null) {
                $unconformities->add($this->exceedingIndexUnconformity($index));
            } else {
                $unconformities->merge($index->checkIntegrity($indexModelFound));
            }
        }
        return $unconformities;
    }

    private function exceedingIndexUnconformity(AbstractMySqlIndex $mySqlIndex)
    {
        $description = "alter table {$this->getTable()} drop index {$mySqlIndex->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlIndex) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} DROP INDEX {$mySqlIndex->getName()}");
        });
        return new Unconformity($description, $instructions);
    }
}

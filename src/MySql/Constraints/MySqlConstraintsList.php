<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IConstraintsListModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;

class MySqlConstraintsList extends ArrayList implements IConstraintsListModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, MySqlTable $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        parent::__construct($this->retrieveConstraints());
    }

    private function retrieveConstraints()
    {
        try {
            $selectExpressions = MySqlPartialConstraint::selectExpressions() ?: "*";
            $stm = $this->pdo->query("
                SELECT $selectExpressions
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE
                    TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME='{$this->getTable()}'
                    AND EXISTS(
                        SELECT 1
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE
                            TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = '{$this->getTable()}'
                            AND CONSTRAINT_NAME = INDEX_NAME
                        LIMIT 1
                    )
            ");
            return $this->groupConstraints($stm->fetchAll(PDO::FETCH_CLASS, MySqlPartialConstraint::class, [$this->pdo]) ?: []);
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

    private function groupConstraints(array $partialConstraints)
    {
        $keys = [];
        $groups = $this->groupPartialConstraints($partialConstraints);
        foreach ($groups as $group) {
            $keys[] = MySqlConstraintFactory::createInstance($this->pdo, $group);
        }
        return $keys;
    }

    private function groupPartialConstraints(array $partialConstraints)
    {
        $groups = [];
        foreach ($partialConstraints as $part) {
            if (!array_key_exists($part->getName(), $groups)) {
                $groups[$part->getName()] = [];
            }
            $groups[$part->getName()][] = $part;
        }
        return $groups;
    }

    public function checkIntegrity(IConstraintsListModel $constraintsListModel)
    {
        return $this->missingConstraintsUnconformities($constraintsListModel)
            ->merge($this->generalConstraintsUnconformities($constraintsListModel));
    }

    public function missingConstraintsUnconformities(IConstraintsListModel $constraintsListModel)
    {
        return new UnconformitiesList();
    }

    public function generalConstraintsUnconformities(IConstraintsListModel $constraintsListModel)
    {
        return new UnconformitiesList();
    }
}

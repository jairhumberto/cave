<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractConstraintsListModel;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\Unconformity;

class MySqlConstraintsList extends AbstractConstraintsListModel
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
            return $this->groupConstraints($stm->fetchAll(PDO::FETCH_CLASS, MySqlPartialConstraint::class, [$this->pdo, $this->table]) ?: []);
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

    protected function missingConstraintUnconformity(IConstraintModel $constraintModel)
    {
        $description = "alter table {$this->getTable()} add {$constraintModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($constraintModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $constraintModel");
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingConstraintUnconformity(AbstractConstraintModel $mySqlConstraint)
    {
        $description = "alter table {$this->getTable()} {$mySqlConstraint->dropCommand()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlConstraint) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} {$mySqlConstraint->dropCommand()}");
        });
        return new Unconformity($description, $instructions);
    }
}

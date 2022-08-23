<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\Models\IConstraintsListModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

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

    private function missingConstraintsUnconformities(IConstraintsListModel $constraintsListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($constraintsListModel as $constraintModel) {
            $callback = function ($item) use ($constraintModel) {
                return $item->getName() == $constraintModel->getName();
            };

            $constraintFound = $this->search($callback);

            if ($constraintFound == null) {
                $unconformities->add($this->missingConstraintUnconformity($constraintModel));
            }
        }
        return $unconformities;
    }

    private function missingConstraintUnconformity(IConstraintModel $constraintModel)
    {
        $description = "alter table {$this->getTable()} add {$constraintModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($constraintModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $constraintModel");
        });
        return new Unconformity($description, $instructions);
    }

    private function generalConstraintsUnconformities(IConstraintsListModel $constraintsListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $constraint) {
            $callback = function ($item) use ($constraint) {
                return $item->getName() == $constraint->getName();
            };

            $constraintModelFound = $constraintsListModel->search($callback);

            if ($constraintModelFound == null) {
                $unconformities->add($this->exceedingConstraintUnconformity($constraint));
            } else {
                $unconformities->merge($constraint->checkIntegrity($constraintModelFound));
            }
        }
        return $unconformities;
    }

    private function exceedingConstraintUnconformity(AbstractMySqlConstraint $mySqlConstraint)
    {
        $description = "alter table {$this->getTable()} {$mySqlConstraint->dropCommand()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlConstraint) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} {$mySqlConstraint->dropCommand()}");
        });
        return new Unconformity($description, $instructions);
    }
}

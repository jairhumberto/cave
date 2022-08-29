<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractMySqlConstraint extends ArrayList implements IConstraintModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->pdo = $pdo;
        $this->table = $partialConstraints[0]->getTable();
        parent::__construct($partialConstraints);
    }

    public function getTable()
    {
        return $this->table;
    }

    private function partialKeysIncompatible(IConstraintModel $constraintModel)
    {
        return $this->count() != $constraintModel->count()
            || $this->constraintsPartsMissing($constraintModel)
            || $this->constraintsPartsExceeding($constraintModel);
    }

    private function constraintsPartsMissing(IConstraintModel $constraintModel)
    {
        foreach ($constraintModel as $key => $constraintPartModel) {
            $currentConstraintPart = $this->get($key);
            if (!$currentConstraintPart->equals($constraintPartModel)) {
                return true;
            }
        }
        return false;
    }

    private function constraintsPartsExceeding(IConstraintModel $constraintModel)
    {
        foreach ($this as $key => $constraintPart) {
            $currentConstraintPartModel = $constraintModel->get($key);
            if (!$currentConstraintPartModel->equals($constraintPart)) {
                return true;
            }
        }
        return false;
    }

    private function incompatibleConstraintUnconformity(IConstraintModel $constraintModel)
    {
        $description = "alter table {$this->getTable()} {$this->dropCommand()}";
        $instructions = new InstructionsList();
        $instructions->add(function () {
            $this->pdo->query("ALTER TABLE {$this->getTable()} {$this->dropCommand()}");
        });
        $instructions->add(function () use ($constraintModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $constraintModel");
        });
        return new Unconformity($description, $instructions);
    }

    public function checkIntegrity(IConstraintModel $constraintModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->partialKeysIncompatible($constraintModel)) {
            $unconformities->add($this->incompatibleConstraintUnconformity($constraintModel));
        }

        return $unconformities;
    }

    /**
     * @return string
     */
    abstract public function dropCommand();
}

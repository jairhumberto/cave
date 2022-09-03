<?php

namespace Squille\Cave\Models;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractConstraintsListModel extends ArrayList implements IConstraintsListModel
{
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

    abstract protected function missingConstraintUnconformity(IConstraintModel $constraintModel);

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

    abstract protected function exceedingConstraintUnconformity(AbstractConstraintModel $mySqlConstraint);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;

abstract class AbstractConstraintsListModel extends ArrayList implements ConstraintsListModelInterface
{
    public function checkIntegrity(ConstraintsListModelInterface $constraintsListModel)
    {
        return $this->missingConstraintsUnconformities($constraintsListModel)
            ->merge($this->generalConstraintsUnconformities($constraintsListModel));
    }

    private function missingConstraintsUnconformities(ConstraintsListModelInterface $constraintsListModel)
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

    abstract protected function missingConstraintUnconformity(ConstraintModelInterface $constraintModel);

    private function generalConstraintsUnconformities(ConstraintsListModelInterface $constraintsListModel)
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

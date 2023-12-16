<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractConstraintModel extends ArrayList implements ConstraintModelInterface
{
    public function checkIntegrity(ConstraintModelInterface $constraintModel): UnconformitiesList
    {
        $unconformities = new UnconformitiesList();
        if ($this->partialKeysIncompatible($constraintModel)) {
            $unconformities->add($this->incompatibleConstraintUnconformity($constraintModel));
        }
        return $unconformities;
    }

    private function partialKeysIncompatible(ConstraintModelInterface $constraintModel): bool
    {
        return $this->count() != $constraintModel->count()
            || $this->constraintsPartsMissing($constraintModel)
            || $this->constraintsPartsExceeding($constraintModel);
    }

    private function constraintsPartsMissing(ConstraintModelInterface $constraintModel): bool
    {
        foreach ($constraintModel as $key => $constraintPartModel) {
            $currentConstraintPart = $this->get($key);
            if (!$currentConstraintPart->equals($constraintPartModel)) {
                return true;
            }
        }
        return false;
    }

    private function constraintsPartsExceeding(ConstraintModelInterface $constraintModel): bool
    {
        foreach ($this as $key => $constraintPart) {
            $currentConstraintPartModel = $constraintModel->get($key);
            if (!$currentConstraintPartModel->equals($constraintPart)) {
                return true;
            }
        }
        return false;
    }

    abstract protected function incompatibleConstraintUnconformity(ConstraintModelInterface $constraintModel): Unconformity;
}

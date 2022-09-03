<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;

abstract class AbstractConstraintModel extends ArrayList implements IConstraintModel
{
    public function checkIntegrity(IConstraintModel $constraintModel)
    {
        $unconformities = new UnconformitiesList();
        if ($this->partialKeysIncompatible($constraintModel)) {
            $unconformities->add($this->incompatibleConstraintUnconformity($constraintModel));
        }
        return $unconformities;
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

    abstract protected function incompatibleConstraintUnconformity(IConstraintModel $constraintModel);
}

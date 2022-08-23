<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface IConstraintModel extends IList
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param IConstraintModel $constraintModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IConstraintModel $constraintModel);
}

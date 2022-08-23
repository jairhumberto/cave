<?php

namespace Squille\Cave\MySql\Constraints;

use Squille\Cave\ArrayList;
use Squille\Cave\Models\IConstraintModel;

abstract class AbstractMySqlConstraint extends ArrayList implements IConstraintModel
{
    /**
     * @return string
     */
    abstract public function dropCommand();
}

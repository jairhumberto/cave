<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface ConstraintsListModelInterface extends ListInterface
{
    public function getTable(): TableModelInterface;
    public function checkIntegrity(ConstraintsListModelInterface $constraintsListModel): UnconformitiesList;
}

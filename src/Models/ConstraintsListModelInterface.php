<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface ConstraintsListModelInterface extends ListInterface
{
    /**
     * @return TableModelInterface
     */
    public function getTable();

    /**
     * @param ConstraintsListModelInterface $constraintsListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(ConstraintsListModelInterface $constraintsListModel);
}

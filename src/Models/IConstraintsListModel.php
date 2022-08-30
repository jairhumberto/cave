<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface IConstraintsListModel extends IList
{
    /**
     * @return ITableModel
     */
    public function getTable();

    /**
     * @param IConstraintsListModel $constraintsListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IConstraintsListModel $constraintsListModel);
}

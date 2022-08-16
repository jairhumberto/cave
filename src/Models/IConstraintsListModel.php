<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

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

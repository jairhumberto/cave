<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface IIndexesListModel extends IList
{
    /**
     * @return ITableModel
     */
    public function getTable();

    /**
     * @param IIndexesListModel $indexesListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IIndexesListModel $indexesListModel);
}

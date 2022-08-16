<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

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

<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface ITablesListModel extends IList
{
    /**
     * @param ITablesListModel $tablesListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(ITablesListModel $tablesListModel);
}

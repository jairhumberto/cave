<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

interface ITablesListModel extends IList
{
    /**
     * @param ITablesListModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(ITablesListModel $model);
}

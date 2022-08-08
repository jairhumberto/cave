<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

interface IFieldsListModel extends IList
{
    /**
     * @param IFieldsListModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(IFieldsListModel $model);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

interface IKeysListModel extends IList
{
    /**
     * @param IKeysListModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(IKeysListModel $model);
}

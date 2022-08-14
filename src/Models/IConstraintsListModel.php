<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

interface IConstraintsListModel extends IList
{
    /**
     * @param IConstraintsListModel $constraintsListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IConstraintsListModel $constraintsListModel);
}

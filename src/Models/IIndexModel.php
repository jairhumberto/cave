<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface IIndexModel extends IList
{
    /**
     * @param IIndexModel $indexModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IIndexModel $indexModel);
}

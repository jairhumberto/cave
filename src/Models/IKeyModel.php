<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface IKeyModel extends IList
{
    /**
     * @param IKeyModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(IKeyModel $model);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

interface IIndexModel extends IList
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param IIndexModel $indexModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IIndexModel $indexModel);
}

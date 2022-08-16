<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IPartialIndexModel
{
    /**
     * @return string
     */
    public function getIndexName();

    /**
     * @param IPartialIndexModel $partialIndexModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IPartialIndexModel $partialIndexModel);
}

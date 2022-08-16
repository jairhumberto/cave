<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IPartialConstraintModel
{
    /**
     * @return string
     */
    public function getConstraintName();

    /**
     * @param IPartialConstraintModel $partialConstraintModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IPartialConstraintModel $partialConstraintModel);
}

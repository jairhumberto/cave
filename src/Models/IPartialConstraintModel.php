<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IPartialConstraintModel
{
    /**
     * @return string
     */
    public function getColumn();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getType();

    /**
     * @param IPartialConstraintModel $partialConstraintModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IPartialConstraintModel $partialConstraintModel);
}

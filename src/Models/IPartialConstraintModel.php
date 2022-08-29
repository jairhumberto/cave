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
     * @return string
     */
    public function getTable();

    /**
     * @param IPartialConstraintModel $partialConstraintModel
     * @return bool
     */
    public function equals(IPartialConstraintModel $partialConstraintModel);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface ConstraintModelInterface extends ListInterface
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
     * @param ConstraintModelInterface $constraintModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(ConstraintModelInterface $constraintModel);
}

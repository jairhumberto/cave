<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface ConstraintModelInterface extends ListInterface
{
    public function getName(): string;
    public function getTable(): string;
    public function checkIntegrity(ConstraintModelInterface $constraintModel): UnconformitiesList;
}

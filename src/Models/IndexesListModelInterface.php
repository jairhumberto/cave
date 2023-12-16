<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface IndexesListModelInterface extends ListInterface
{
    public function getTable(): TableModelInterface;
    public function checkIntegrity(IndexesListModelInterface $indexesListModel): UnconformitiesList;
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface IndexesListModelInterface extends ListInterface
{
    /**
     * @return TableModelInterface
     */
    public function getTable();

    /**
     * @param IndexesListModelInterface $indexesListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IndexesListModelInterface $indexesListModel);
}

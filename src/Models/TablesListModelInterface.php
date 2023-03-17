<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface TablesListModelInterface extends ListInterface
{
    /**
     * @param TablesListModelInterface $tablesListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(TablesListModelInterface $tablesListModel);
}

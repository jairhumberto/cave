<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface TablesListModelInterface extends ListInterface
{
    public function checkIntegrity(TablesListModelInterface $tablesListModel): UnconformitiesList;
}

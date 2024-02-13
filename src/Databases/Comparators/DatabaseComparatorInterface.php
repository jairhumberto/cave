<?php

namespace Squille\Databases\Comparators;

use Squille\Databases\Models\Database;
use Squille\ArrayList;

interface DatabaseComparatorInterface
{
    public function compare(Database $target, Database $model): ArrayList;
}

<?php

namespace Squille\Tables\Comparators;

use ArrayObject;
use Squille\Tables\Models\Table;

interface TableComparatorInterface
{
    /**
     * @return ArrayObject<Unconformity>
     */
    public function compare(Table $target, Table $model): ArrayObject;
}

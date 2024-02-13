<?php

namespace Squille\Databases\Comparators;

use Squille\Databases\Models\Database;
use Squille\Instruction;
use Squille\Unconformity;
use Squille\ArrayList;

abstract class AbstractDatabaseComparator implements DatabaseComparatorInterface
{
    public function compare(Database $target, Database $model): ArrayList
    {
        $unconformities = new ArrayList();
        if ($target->getCollation() != $model->getCollation()) {
            $unconformities->append($this->collationUnconformity($target, $model));
        }
        return $unconformities;
    }

    private function collationUnconformity(Database $target, Database $model): Unconformity
    {
        $description = "alter database collate {{$target->getCollation()} -> {$model->getCollation()}}";

        $instructions = new ArrayList();
        $instructions->append($this->getCollationChangeInstruction($model));

        return new Unconformity($description, $instructions);
    }

    abstract protected function getCollationChangeInstruction(Database $model): Instruction;
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

abstract class AbstractDatabaseModel implements IDatabaseModel
{
    public function checkIntegrity(IDatabaseModel $databaseModel)
    {
        $unconformities = new UnconformitiesList();
        if ($this->getCollation() != $databaseModel->getCollation()) {
            $unconformities->add($this->collationUnconformity($databaseModel));
        }
        return $unconformities->merge($this->getTables()->checkIntegrity($databaseModel->getTables()));
    }

    abstract protected function collationUnconformity(IDatabaseModel $databaseModel);
}

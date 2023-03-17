<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractDatabaseModel implements DatabaseModelInterface
{
    public function checkIntegrity(DatabaseModelInterface $databaseModel)
    {
        $unconformities = new UnconformitiesList();
        if ($this->getCollation() != $databaseModel->getCollation()) {
            $unconformities->add($this->collationUnconformity($databaseModel));
        }
        return $unconformities->merge($this->getTables()->checkIntegrity($databaseModel->getTables()));
    }

    /**
     * @param DatabaseModelInterface $databaseModel
     * @return Unconformity
     */
    abstract protected function collationUnconformity(DatabaseModelInterface $databaseModel);
}

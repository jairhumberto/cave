<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface DatabaseModelInterface
{
    /**
     * @return string
     */
    public function getCollation();

    /**
     * @return TablesListModelInterface
     */
    public function getTables();

    /**
     * @param DatabaseModelInterface $databaseModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(DatabaseModelInterface $databaseModel);
}

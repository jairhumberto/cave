<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface DatabaseModelInterface
{
    public function getCollation(): string;
    public function getTables(): TablesListModelInterface;

    public function checkIntegrity(DatabaseModelInterface $databaseModel): UnconformitiesList;
}

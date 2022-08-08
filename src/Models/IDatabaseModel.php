<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IDatabaseModel
{
    /**
     * @return string
     */
    public function getCharset();

    /**
     * @return string
     */
    public function getCollation();

    /**
     * @return ITablesListModel
     */
    public function getTables();

    /**
     * @param IDatabaseModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(IDatabaseModel $model);
}

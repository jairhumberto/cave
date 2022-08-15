<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\IList;

interface IFieldsListModel extends IList
{
    /**
     * @return ITableModel
     */
    public function getTable();

    /**
     * @param IFieldsListModel $fieldsListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IFieldsListModel $fieldsListModel);
}

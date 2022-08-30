<?php

namespace Squille\Cave\Models;

use Squille\Cave\IList;
use Squille\Cave\UnconformitiesList;

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

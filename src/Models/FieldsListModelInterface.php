<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface FieldsListModelInterface extends ListInterface
{
    /**
     * @return TableModelInterface
     */
    public function getTable();

    /**
     * @param FieldsListModelInterface $fieldsListModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(FieldsListModelInterface $fieldsListModel);
}

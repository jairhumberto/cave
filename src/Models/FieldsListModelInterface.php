<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface FieldsListModelInterface extends ListInterface
{
    public function getTable(): TableModelInterface;
    public function checkIntegrity(FieldsListModelInterface $fieldsListModel): UnconformitiesList;
}

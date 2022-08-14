<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface ITableModel
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getEngine();

    /**
     * @return string
     */
    public function getRowFormat();

    /**
     * @return string
     */
    public function getCollation();

    /**
     * @return string
     */
    public function getChecksum();

    /**
     * @return IFieldsListModel
     */
    public function getFields();

    /**
     * @return IConstraintsListModel
     */
    public function getConstraints();

    /**
     * @return IIndexesListModel
     */
    public function getIndexes();

    /**
     * @param ITableModel $tableModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(ITableModel $tableModel);
}

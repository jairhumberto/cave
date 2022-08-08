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
     * @return IKeysListModel
     */
    public function getKeys();

    /**
     * @param ITableModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(ITableModel $model);
}

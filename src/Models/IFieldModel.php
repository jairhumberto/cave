<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IFieldModel
{
    /**
     * @return ITableModel
     */
    public function getTable();

    /**
     * @return string
     */
    public function getField();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getCollation();

    /**
     * @return string
     */
    public function getNull();

    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getDefault();

    /**
     * @return string
     */
    public function getExtra();

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param IFieldModel $fieldModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IFieldModel $fieldModel);
}

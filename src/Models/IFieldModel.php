<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IFieldModel
{
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
     * @param IFieldModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(IFieldModel $model);
}

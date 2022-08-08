<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface IKeyPartModel
{
    /**
     * @return int
     */
    public function getNonUnique();

    /**
     * @return string
     */
    public function getKeyName();

    /**
     * @return int
     */
    public function getSeqInIndex();

    /**
     * @return string
     */
    public function getColumnName();

    /**
     * @return string
     */
    public function getCollation();

    /**
     * @return string
     */
    public function getSubPart();

    /**
     * @return string
     */
    public function getPacked();

    /**
     * @return string
     */
    public function getNull();

    /**
     * @return string
     */
    public function getIndexType();

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param IKeyPartModel $model
     * @return UnconformitiesList
     */
    public function checkIntegrity(IKeyPartModel $model);
}

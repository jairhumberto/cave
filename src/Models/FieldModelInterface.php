<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface FieldModelInterface
{
    /**
     * @return TableModelInterface
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
     * @param FieldModelInterface $fieldModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(FieldModelInterface $fieldModel);
}

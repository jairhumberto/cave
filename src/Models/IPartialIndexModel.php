<?php

namespace Squille\Cave\Models;

interface IPartialIndexModel
{
    /**
     * @return string
     */
    public function getColumn();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param IPartialIndexModel $partialIndexModel
     * @return bool
     */
    public function equals(IPartialIndexModel $partialIndexModel);
}

<?php

namespace Squille\Cave\Models;

interface PartialConstraintModelInterface
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
     * @param PartialConstraintModelInterface $partialConstraintModel
     * @return bool
     */
    public function equals(PartialConstraintModelInterface $partialConstraintModel);
}

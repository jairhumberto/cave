<?php

namespace Squille\Cave\Models;

interface PartialIndexModelInterface
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
     * @param PartialIndexModelInterface $partialIndexModel
     * @return bool
     */
    public function equals(PartialIndexModelInterface $partialIndexModel);
}

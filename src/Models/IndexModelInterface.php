<?php

namespace Squille\Cave\Models;

use Squille\Cave\ListInterface;
use Squille\Cave\UnconformitiesList;

interface IndexModelInterface extends ListInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param IndexModelInterface $indexModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(IndexModelInterface $indexModel);
}

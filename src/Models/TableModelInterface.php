<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface TableModelInterface
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
     * @return FieldsListModelInterface
     */
    public function getFields();

    /**
     * @return ConstraintsListModelInterface
     */
    public function getConstraints();

    /**
     * @return IndexesListModelInterface
     */
    public function getIndexes();

    /**
     * @param TableModelInterface $tableModel
     * @return UnconformitiesList
     */
    public function checkIntegrity(TableModelInterface $tableModel);
}

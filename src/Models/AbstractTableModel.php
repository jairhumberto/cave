<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractTableModel implements ITableModel
{
    public function checkIntegrity(ITableModel $tableModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getEngine() != $tableModel->getEngine()) {
            $unconformities->add($this->engineUnconformity($tableModel));
        }

        if ($this->getRowFormat() != $tableModel->getRowFormat()) {
            $unconformities->add($this->rowFormatUnconformity($tableModel));
        }

        if ($this->getCollation() != $tableModel->getCollation()) {
            $unconformities->add($this->collateUnconformity($tableModel));
        }

        if ($this->getChecksum() != $tableModel->getChecksum()) {
            $unconformities->add($this->checksumUnconformity($tableModel));
        }

        return $unconformities
            ->merge($this->getFields()->checkIntegrity($tableModel->getFields()))
            ->merge($this->getConstraints()->checkIntegrity($tableModel->getConstraints()))
            ->merge($this->getIndexes()->checkIntegrity($tableModel->getIndexes()));
    }

    /**
     * @param ITableModel $tableModel
     * @return Unconformity
     */
    abstract protected function engineUnconformity(ITableModel $tableModel);

    /**
     * @param ITableModel $tableModel
     * @return Unconformity
     */
    abstract protected function rowFormatUnconformity(ITableModel $tableModel);

    /**
     * @param ITableModel $tableModel
     * @return Unconformity
     */
    abstract protected function collateUnconformity(ITableModel $tableModel);

    /**
     * @param ITableModel $tableModel
     * @return Unconformity
     */
    abstract protected function checksumUnconformity(ITableModel $tableModel);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractTableModel implements TableModelInterface
{
    public function checkIntegrity(TableModelInterface $tableModel): UnconformitiesList
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

    abstract protected function engineUnconformity(TableModelInterface $tableModel): Unconformity;
    abstract protected function rowFormatUnconformity(TableModelInterface $tableModel): Unconformity;
    abstract protected function collateUnconformity(TableModelInterface $tableModel): Unconformity;
    abstract protected function checksumUnconformity(TableModelInterface $tableModel): Unconformity;
}

<?php

namespace Squille\Tables\Comparators;

use ArrayObject;
use Squille\Cave\Models\TableModelInterface;
use Squille\Schemas\Models\Database;
use Squille\Tables\Models\Table;

abstract class AbstractTableComparator implements SchemaComparatorInterface
{
    private AbstractTablesListComparator $tablesListComparator;

    public function __construct(AbstractTablesListComparator $tablesListComparator)
    {
        $this->tablesListComparator = $tablesListComparator;
    }

    public function compare(Table $target, Table $model): ArrayObject
    {
        $unconformities = new ArrayObject();

        if ($target->getEngine() != $model->getEngine()) {
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

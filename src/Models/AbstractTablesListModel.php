<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractTablesListModel extends ArrayList implements TablesListModelInterface
{
    public function checkIntegrity(TablesListModelInterface $tablesListModel): UnconformitiesList
    {
        return $this->missingTablesUnconformities($tablesListModel)
            ->merge($this->generalTablesUnconformities($tablesListModel));
    }

    private function missingTablesUnconformities(TablesListModelInterface $tablesListModel): UnconformitiesList
    {
        $unconformities = new UnconformitiesList();
        foreach ($tablesListModel as $tableModel) {
            $callback = function ($item) use ($tableModel) {
                return $item->getName() == $tableModel->getName();
            };
            $tableFound = $this->search($callback);
            if ($tableFound == null) {
                $unconformities->add($this->missingTableUnconformity($tableModel));
            }
        }
        return $unconformities;
    }

    abstract protected function missingTableUnconformity(TableModelInterface $tableModel): Unconformity;

    private function generalTablesUnconformities(TablesListModelInterface $tablesListModel): UnconformitiesList
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $table) {
            $callback = function ($item) use ($table) {
                return $item->getName() == $table->getName();
            };
            $tableModelFound = $tablesListModel->search($callback);
            if ($tableModelFound == null) {
                $unconformities->add($this->exceedingTableUnconformity($table));
            } else {
                $unconformities->merge($table->checkIntegrity($tableModelFound));
            }
        }
        return $unconformities;
    }

    abstract protected function exceedingTableUnconformity(AbstractTableModel $table): Unconformity;
}

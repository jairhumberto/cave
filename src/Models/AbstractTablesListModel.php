<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractTablesListModel extends ArrayList implements TablesListModelInterface
{
    public function checkIntegrity(TablesListModelInterface $tablesListModel)
    {
        return $this->missingTablesUnconformities($tablesListModel)
            ->merge($this->generalTablesUnconformities($tablesListModel));
    }

    private function missingTablesUnconformities(TablesListModelInterface $tablesListModel)
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

    /**
     * @param TableModelInterface $tableModel
     * @return Unconformity
     */
    abstract protected function missingTableUnconformity(TableModelInterface $tableModel);

    private function generalTablesUnconformities(TablesListModelInterface $tablesListModel)
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

    /**
     * @param AbstractTableModel $table
     * @return Unconformity
     */
    abstract protected function exceedingTableUnconformity(AbstractTableModel $table);
}

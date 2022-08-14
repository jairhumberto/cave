<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\Models\ITablesListModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlTablesList extends ArrayList implements ITablesListModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        parent::__construct($this->retrieveTables());
    }

    private function retrieveTables()
    {
        try {
            $stm = $this->pdo->query("SHOW TABLE STATUS");
            return $stm->fetchAll(PDO::FETCH_CLASS, MySqlTable::class, [$this->pdo]) ?: [];
        } finally {
            if ($stm instanceof PDOStatement) {
                $stm->closeCursor();
            }
        }
    }

    public function checkIntegrity(ITablesListModel $tablesListModel)
    {
        return $this->missingTablesUnconformities($tablesListModel)
            ->merge($this->generalTablesUnconformities($tablesListModel));
    }

    private function missingTablesUnconformities(ITablesListModel $tablesListModel)
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

    private function missingTableUnconformity(ITableModel $modelTable)
    {
        $description = "create table {$modelTable->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($modelTable) {
            $tblName = $modelTable->getName();
            $createDefinitions = $modelTable->getFields()
                ->merge($modelTable->getConstraints())
                ->merge($modelTable->getIndexes());
            $tableOptions = $this->getTableOptions($modelTable);
            $this->pdo->query("
                CREATE TABLE $tblName
                ($createDefinitions) $tableOptions
            ");
        });
        return new Unconformity($description, $instructions);
    }

    private function getTableOptions(ITableModel $modelTable)
    {
        $tableOptionsArray = [
            "ENGINE {$modelTable->getEngine()}",
            "ROW_FORMAT {$modelTable->getRowFormat()}",
            "COLLATE {$modelTable->getCollation()}"
        ];
        if ($modelTable->getChecksum()) {
            $tableOptionsArray[] = "CHECKSUM {$modelTable->getChecksum()}";
        }
        return join(",", $tableOptionsArray);
    }

    private function generalTablesUnconformities(ITablesListModel $tablesListModel)
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

    private function exceedingTableUnconformity(MySqlTable $mySqlTable)
    {
        $description = "drop table {$mySqlTable->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlTable) {
            $this->pdo->query("DROP TABLE {$mySqlTable->getName()}");
        });
        return new Unconformity($description, $instructions);
    }
}

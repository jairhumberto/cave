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
            $result = $this->pdo->query("SHOW TABLE STATUS");
            return $result->fetchAll(PDO::FETCH_CLASS, MySqlTable::class, [$this->pdo]) ?: [];
        } finally {
            if ($result instanceof PDOStatement) {
                $result->closeCursor();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(ITablesListModel $model)
    {
        return $this->missingTablesUnconformities($model)
            ->merge($this->generalTablesUnconformities($model));
    }

    private function missingTablesUnconformities(ITablesListModel $tableListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($tableListModel as $tableModel) {
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
        $description = "create table `{$modelTable->getName()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($modelTable) {
            $tblName = $modelTable->getName();
            $createDefinitions = $modelTable->getFields()->merge($modelTable->getKeys());
            $tableOptions = $this->getTableOptions($modelTable);
            $this->pdo->query("
                CREATE TABLE `$tblName`
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
            $tableOptionsArray[] = "CHECKSUM `{$modelTable->getChecksum()}`";
        }
        return join(",", $tableOptionsArray);
    }

    private function generalTablesUnconformities(ITablesListModel $model)
    {
        $unconformities = new UnconformitiesList();

        /** @var MySqlTable $table */
        foreach ($this as $table) {
            $callback = function ($item) use ($table) {
                return $item->getName() == $table->getName();
            };

            $exceedingTableFound = $model->search($callback);

            if ($exceedingTableFound == null) {
                $unconformities->add($this->exceedingTableUnconformity($table));
            } else {
                $unconformities->merge($table->checkIntegrity($exceedingTableFound));
            }
        }

        return $unconformities;
    }

    private function exceedingTableUnconformity(ITableModel $table)
    {
        $description = "drop table `{$table->getName()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($table) {
            $this->pdo->query("DROP TABLE `{$table->getName()}`");
        });
        return new Unconformity($description, $instructions);
    }
}

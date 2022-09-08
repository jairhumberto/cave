<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractTableModel;
use Squille\Cave\Models\AbstractTablesListModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\Unconformity;

class MySqlTablesList extends AbstractTablesListModel
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

    protected function missingTableUnconformity(ITableModel $tableModel)
    {
        $description = "create table {$tableModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $tblName = $tableModel->getName();
            $createDefinitions = $tableModel->getFields()
                ->merge($tableModel->getConstraints())
                ->merge($tableModel->getIndexes());
            $tableOptions = $this->getTableOptions($tableModel);
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
            $tableOptionsArray[] = "CHECKSUM {$modelTable->getChecksum()}";
        }
        return join(",", $tableOptionsArray);
    }

    protected function exceedingTableUnconformity(AbstractTableModel $abstractTableModel)
    {
        $description = "drop table {$abstractTableModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($abstractTableModel) {
            $this->pdo->query("DROP TABLE {$abstractTableModel->getName()}");
        });
        return new Unconformity($description, $instructions);
    }
}

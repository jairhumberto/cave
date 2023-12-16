<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractTableModel;
use Squille\Cave\Models\AbstractTablesListModel;
use Squille\Cave\Models\TableModelInterface;
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

    protected function missingTableUnconformity(TableModelInterface $tableModel): Unconformity
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

    private function getTableOptions(TableModelInterface $tableModel): string
    {
        $tableOptionsArray = [
            "ENGINE {$tableModel->getEngine()}",
            "ROW_FORMAT {$tableModel->getRowFormat()}",
            "COLLATE {$tableModel->getCollation()}"
        ];
        if ($tableModel->getChecksum()) {
            $tableOptionsArray[] = "CHECKSUM {$tableModel->getChecksum()}";
        }
        return join(",", $tableOptionsArray);
    }

    protected function exceedingTableUnconformity(AbstractTableModel $table): Unconformity
    {
        $description = "drop table {$table->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($table) {
            $this->pdo->query("DROP TABLE {$table->getName()}");
        });
        return new Unconformity($description, $instructions);
    }
}

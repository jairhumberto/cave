<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\MOdels\AbstractTableModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\MySql\Constraints\MySqlConstraintsList;
use Squille\Cave\MySql\Indexes\MySqlIndexesList;
use Squille\Cave\Unconformity;

class MySqlTable extends AbstractTableModel
{
    private $pdo;
    private $fields;
    private $constraints;
    private $indexes;
    private $Name;
    private $Engine;
    private $Row_format;
    private $Collation;
    private $Checksum;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->fields = new MySqlFieldsList($this->pdo, $this);
        $this->constraints = new MySqlConstraintsList($this->pdo, $this);
        $this->indexes = new MySqlIndexesList($this->pdo, $this);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->Name;
    }

    protected function engineUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel engine {{$this->getEngine()} -> {$tableModel->getEngine()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE $tableModel
                ENGINE {$tableModel->getEngine()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getEngine()
    {
        return $this->Engine;
    }

    protected function rowFormatUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel row_format {{$this->getRowFormat()} -> {$tableModel->getRowFormat()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE $tableModel
                ROW_FORMAT {$tableModel->getRowFormat()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getRowFormat()
    {
        return $this->Row_format;
    }

    protected function collateUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel collate {{$this->getCollation()} -> {$tableModel->getCollation()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE $tableModel
                COLLATE {$tableModel->getCollation()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    protected function checksumUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel checksum {{$this->getChecksum()} -> {$tableModel->getChecksum()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE $tableModel
                CHECKSUM {$tableModel->getChecksum()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getChecksum()
    {
        return $this->Checksum;
    }
}

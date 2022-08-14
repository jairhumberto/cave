<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\MySql\Constraints\MySqlConstraintsList;
use Squille\Cave\MySql\Indexes\MySqlIndexesList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlTable implements ITableModel
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

    public function getEngine()
    {
        return $this->Engine;
    }

    private function engineUnconformity(ITableModel $tableModel)
    {
        $description = "alter table {$tableModel->getName()} engine {{$this->getEngine()} -> {$tableModel->getEngine()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE {$tableModel->getName()}
                ENGINE {$tableModel->getEngine()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getName()
    {
        return $this->Name;
    }

    public function getRowFormat()
    {
        return $this->Row_format;
    }

    private function rowFormatUnconformity(ITableModel $tableModel)
    {
        $description = "alter table {$tableModel->getName()} row_format {{$this->getRowFormat()} -> {$tableModel->getRowFormat()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE {$tableModel->getName()}
                ROW_FORMAT {$tableModel->getRowFormat()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    private function collateUnconformity(ITableModel $tableModel)
    {
        $description = "alter table {$tableModel->getName()} collate {{$this->getCollation()} -> {$tableModel->getCollation()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE {$tableModel->getName()}
                COLLATE {$tableModel->getCollation()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getChecksum()
    {
        return $this->Checksum;
    }

    private function checksumUnconformity(ITableModel $tableModel)
    {
        $description = "alter table {$tableModel->getName()} checksum {{$this->getChecksum()} -> {$tableModel->getChecksum()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->pdo->query("
                ALTER TABLE {$tableModel->getName()}
                CHECKSUM {$tableModel->getChecksum()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }
}

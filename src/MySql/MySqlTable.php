<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\MOdels\AbstractTableModel;
use Squille\Cave\Models\ConstraintsListModelInterface;
use Squille\Cave\Models\FieldsListModelInterface;
use Squille\Cave\Models\IndexesListModelInterface;
use Squille\Cave\Models\TableModelInterface;
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

    public function getFields(): FieldsListModelInterface
    {
        return $this->fields;
    }

    public function getConstraints(): ConstraintsListModelInterface
    {
        return $this->constraints;
    }

    public function getIndexes(): IndexesListModelInterface
    {
        return $this->indexes;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->Name;
    }

    protected function engineUnconformity(TableModelInterface $tableModel): Unconformity
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

    public function getEngine(): string
    {
        return $this->Engine;
    }

    protected function rowFormatUnconformity(TableModelInterface $tableModel): Unconformity
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

    public function getRowFormat(): string
    {
        return $this->Row_format;
    }

    protected function collateUnconformity(TableModelInterface $tableModel): Unconformity
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

    public function getCollation(): string
    {
        return $this->Collation;
    }

    protected function checksumUnconformity(TableModelInterface $tableModel): Unconformity
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

    public function getChecksum(): string
    {
        return $this->Checksum;
    }
}

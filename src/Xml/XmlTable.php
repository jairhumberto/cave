<?php

namespace Squille\Cave\Xml;

use DOMElement;
use Squille\Cave\InstructionsList;
use Squille\Cave\MOdels\AbstractTableModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\Unconformity;

class XmlTable extends AbstractTableModel
{
    private $root;
    private $fields;
    private $constraints;
    private $indexes;
    private $Name;
    private $Engine;
    private $Row_format;
    private $Collation;
    private $Checksum;

    public function __construct(DOMElement $element)
    {
        $this->root = $element;
        $this->fields = new XmlFieldsList($element, $this);
        $this->constraints = new XmlConstraintsList($element, $this);
        $this->indexes = new XmlIndexesList($element, $this);
    }

    public function getName()
    {
        return $this->Name;
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

    protected function engineUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel engine {{$this->getEngine()} -> {$tableModel->getEngine()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->root->setAttribute("engine", $tableModel->getEngine());
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
            $this->root->setAttribute("row_format", $tableModel->getRowFormat());
        });
        return new Unconformity($description, $instructions);
    }

    public function getRowFormat()
    {
        return $this->Row_format;
    }

    protected function collateUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel collation {{$this->getCollation()} -> {$tableModel->getCollation()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->root->setAttribute("collation", $tableModel->getCollation());
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
            $this->root->setAttribute("checksum", $tableModel->getChecksum());
        });
        return new Unconformity($description, $instructions);
    }

    public function getChecksum()
    {
        return $this->Checksum;
    }
}

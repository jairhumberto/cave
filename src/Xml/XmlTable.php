<?php

namespace Squille\Cave\Xml;

use DOMElement;
use Squille\Cave\InstructionsList;
use Squille\Cave\MOdels\AbstractTableModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\Unconformity;

class XmlTable extends AbstractTableModel
{
    private $tableElement;
    private $fields;
    private $constraints;
    private $indexes;
    private $name;
    private $engine;
    private $rowFormat;
    private $collation;
    private $checksum;

    public function __construct(DOMElement $tableElement)
    {
        $this->tableElement = $tableElement;
        $this->fields = new XmlFieldsList($tableElement, $this);
        $this->constraints = new XmlConstraintsList($tableElement, $this);
        $this->indexes = new XmlIndexesList($tableElement, $this);
    }

    /**
     * @param DOMElement $xmlTableElement
     * @return XmlTable
     */
    public static function createInstanceFromXmlTableElement(DOMElement $xmlTableElement)
    {
        $instance = new XmlTable($xmlTableElement);
        $instance->setName($xmlTableElement->getAttribute("name"));
        $instance->setEngine($xmlTableElement->getAttribute("engine"));
        $instance->setRowFormat($xmlTableElement->getAttribute("row_format"));
        $instance->setCollation($xmlTableElement->getAttribute("collation"));
        $instance->setChecksum($xmlTableElement->getAttribute("checksum"));
        return $instance;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
            $this->tableElement->setAttribute("engine", $tableModel->getEngine());
        });
        return new Unconformity($description, $instructions);
    }

    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    protected function rowFormatUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel row_format {{$this->getRowFormat()} -> {$tableModel->getRowFormat()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->tableElement->setAttribute("row_format", $tableModel->getRowFormat());
        });
        return new Unconformity($description, $instructions);
    }

    public function getRowFormat()
    {
        return $this->rowFormat;
    }

    /**
     * @param string $rowFormat
     */
    public function setRowFormat($rowFormat)
    {
        $this->rowFormat = $rowFormat;
    }

    protected function collateUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel collation {{$this->getCollation()} -> {$tableModel->getCollation()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->tableElement->setAttribute("collation", $tableModel->getCollation());
        });
        return new Unconformity($description, $instructions);
    }

    public function getCollation()
    {
        return $this->collation;
    }

    /**
     * @param string $collation
     */
    public function setCollation($collation)
    {
        $this->collation = $collation;
    }

    protected function checksumUnconformity(ITableModel $tableModel)
    {
        $description = "alter table $tableModel checksum {{$this->getChecksum()} -> {$tableModel->getChecksum()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $this->tableElement->setAttribute("checksum", $tableModel->getChecksum());
        });
        return new Unconformity($description, $instructions);
    }

    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * @param string $checksum
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;
    }
}

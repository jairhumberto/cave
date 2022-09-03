<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use Squille\Cave\InstructionsList;
use Squille\Cave\MOdels\AbstractTableModel;
use Squille\Cave\Models\AbstractTablesListModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\Unconformity;

class XmlTablesList extends AbstractTablesListModel
{
    private $root;

    public function __construct(DOMElement $parent)
    {
        $this->root = $this->createRootElement($parent);
        parent::__construct($this->retrieveTables());
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode
     */
    private function createRootElement(DOMElement $parent)
    {
        $tables = $this->getRootElement($parent);
        if ($tables == null) {
            $tables = $parent->ownerDocument->createElement("tables");
            $parent->appendChild($tables);
        }
        return $tables;
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode|null
     */
    private function getRootElement(DOMElement $parent)
    {
        foreach ($parent->childNodes as $childNode) {
            if ($childNode->nodeName == "tables") {
                return $childNode;
            }
        }
        return null;
    }

    private function retrieveTables()
    {
        $tables = [];
        foreach ($this->root->childNodes as $childNode) {
            $tables[] = XmlTable::fromDomElement($childNode);
        }
        return $tables;
    }

    protected function missingTableUnconformity(ITableModel $tableModel)
    {
        $description = "create table {$tableModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $tableNode = $this->root->ownerDocument->createElement("table");
            $tableNode->setAttribute("name", $tableModel->getName());
            $tableNode->setAttribute("engine", $tableModel->getEngine());
            $tableNode->setAttribute("row_format", $tableModel->getRowFormat());
            $tableNode->setAttribute("collation", $tableModel->getCollation());
            $tableNode->setAttribute("checksum", $tableModel->getChecksum());
            $this->root->appendChild($tableNode);
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingTableUnconformity(AbstractTableModel $abstractTableModel)
    {
        $description = "drop table {$abstractTableModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($abstractTableModel) {
            foreach ($this->root->childNodes as $childNode) {
                if ($childNode->getAttibute("name") == $abstractTableModel->getName()) {
                    $this->root->removeChild($childNode);
                    break;
                }
            }
        });
        return new Unconformity($description, $instructions);
    }
}

<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use Squille\Cave\InstructionsList;
use Squille\Cave\MOdels\AbstractTableModel;
use Squille\Cave\Models\AbstractTablesListModel;
use Squille\Cave\Models\TableModelInterface;
use Squille\Cave\Unconformity;

class XmlTablesList extends AbstractTablesListModel
{
    private $tablesElement;

    public function __construct(DOMElement $databaseElement)
    {
        $this->tablesElement = $this->retrieveOrCreateTablesElement($databaseElement);
        parent::__construct($this->retrieveTables());
    }

    /**
     * @param DOMElement $databaseElement
     * @return DOMNode
     */
    private function retrieveOrCreateTablesElement(DOMElement $databaseElement)
    {
        $tables = $this->retrieveTablesElement($databaseElement);
        if ($tables == null) {
            $document = $databaseElement->ownerDocument;
            $tables = $databaseElement->appendChild($document->createElement("tables"));
        }
        return $tables;
    }

    /**
     * @param DOMElement $databaseElement
     * @return DOMNode|null
     */
    private function retrieveTablesElement(DOMElement $databaseElement)
    {
        foreach ($databaseElement->childNodes as $childNode) {
            if ($childNode->nodeName == "tables") {
                return $childNode;
            }
        }
        return null;
    }

    private function retrieveTables()
    {
        $tables = [];
        foreach ($this->tablesElement->childNodes as $childNode) {
            $tables[] = XmlTable::createInstanceFromXmlTableElement($childNode);
        }
        return $tables;
    }

    protected function missingTableUnconformity(TableModelInterface $tableModel)
    {
        $description = "create table {$tableModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($tableModel) {
            $tableNode = $this->tablesElement->ownerDocument->createElement("table");
            $tableNode->setAttribute("name", $tableModel->getName());
            $tableNode->setAttribute("engine", $tableModel->getEngine());
            $tableNode->setAttribute("row_format", $tableModel->getRowFormat());
            $tableNode->setAttribute("collation", $tableModel->getCollation());
            $tableNode->setAttribute("checksum", $tableModel->getChecksum());
            $tableNode->appendChild($tableModel->getFields());
            //
            // O problema da toString escrever sql é que agora preciso de xml.
            // Preciso repensar o modelo.
            //
            $tableNode->appendChild($tableModel->getFields());
            $tableNode->appendChild($tableModel->getFields());
            $this->tablesElement->appendChild($tableNode);
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingTableUnconformity(AbstractTableModel $table)
    {
        $description = "drop table {$table->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($table) {
            foreach ($this->tablesElement->childNodes as $childNode) {
                if ($childNode->getAttibute("name") == $table->getName()) {
                    $this->tablesElement->removeChild($childNode);
                    break;
                }
            }
        });
        return new Unconformity($description, $instructions);
    }
}

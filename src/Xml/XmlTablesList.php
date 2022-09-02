<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\Models\ITablesListModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class XmlTablesList extends ArrayList implements ITablesListModel
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
        foreach($this->root->childNodes as $childNode) {
            $tables[] = new XmlTable($childNode);
        }
        return $tables;
    }

    public function checkIntegrity(ITablesListModel $tablesListModel)
    {
        return $this->missingTablesUnconformities($tablesListModel)
            ->merge($this->generalTablesUnconformities($tablesListModel));
    }

    private function missingTablesUnconformities(ITablesListModel $tablesListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($tablesListModel as $tableModel) {
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
        $description = "create table {$modelTable->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($modelTable) {
            $tableNode = $this->root->ownerDocument->createElement("table");
            $tableNode->setAttribute("name", $modelTable->getName());
            $tableNode->setAttribute("engine", $modelTable->getEngine());
            $tableNode->setAttribute("row_format", $modelTable->getRowFormat());
            $tableNode->setAttribute("collation", $modelTable->getCollation());
            $tableNode->setAttribute("checksum", $modelTable->getChecksum());
            $this->root->appendChild($tableNode);
        });
        return new Unconformity($description, $instructions);
    }

    private function generalTablesUnconformities(ITablesListModel $tablesListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $table) {
            $callback = function ($item) use ($table) {
                return $item->getName() == $table->getName();
            };

            $tableModelFound = $tablesListModel->search($callback);

            if ($tableModelFound == null) {
                $unconformities->add($this->exceedingTableUnconformity($table));
            } else {
                $unconformities->merge($table->checkIntegrity($tableModelFound));
            }
        }
        return $unconformities;
    }

    private function exceedingTableUnconformity(XmlTable $xmlTable)
    {
        $description = "drop table {$xmlTable->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($xmlTable) {
            foreach($this->root->childNodes as $childNode) {
                if ($childNode->name == $xmlTable->getName()) {
                    $this->root->removeChild($childNode);
                    break;
                }
            }
        });
        return new Unconformity($description, $instructions);
    }
}

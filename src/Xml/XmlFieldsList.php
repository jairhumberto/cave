<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractFieldModel;
use Squille\Cave\Models\AbstractFieldsListModel;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\Unconformity;

class XmlFieldsList extends AbstractFieldsListModel
{
    private $root;
    private $table;

    public function __construct(DOMElement $parent, XmlTable $table)
    {
        $this->root = $this->createRootElement($parent);
        $this->table = $table;
        parent::__construct($this->retrieveFields());
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode
     */
    private function createRootElement(DOMElement $parent)
    {
        $fields = $this->getRootElement($parent);
        if ($fields == null) {
            $fields = $parent->ownerDocument->createElement("fields");
            $parent->appendChild($fields);
        }
        return $fields;
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode|null
     */
    private function getRootElement(DOMElement $parent)
    {
        foreach ($parent->childNodes as $childNode) {
            if ($childNode->nodeName == "fields") {
                return $childNode;
            }
        }
        return null;
    }

    private function retrieveFields()
    {
        $fields = [];
        foreach ($this->root->childNodes as $childNode) {
            $fields[] = XmlField::fromDomElement($childNode, $this->table);
        }
        return $fields;
    }

    protected function missingFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
    {
        $description = "alter table {$currentFieldModel->getTable()} add {$currentFieldModel->getField()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentFieldModel, $previousFieldModel) {
            $position = $previousFieldModel == null ? "FIRST" : "AFTER {$previousFieldModel->getField()}";
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

    public function getTable()
    {
        return $this->table;
    }

    protected function exceedingFieldUnconformity(AbstractFieldModel $abstractFieldModel)
    {
        $description = "alter table {$abstractFieldModel->getTable()} drop column {$abstractFieldModel->getField()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($abstractFieldModel) {
            foreach ($this->root->childNodes as $childNode) {
                if ($childNode->getAttribute("field") == $abstractFieldModel->getField()) {
                    $this->root->removeChild($childNode);
                    break;
                }
            }
        });
        return new Unconformity($description, $instructions);
    }

    protected function orderFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
    {
        $description = "alter table {$currentFieldModel->getTable()} modify {$currentFieldModel->getField()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentFieldModel, $previousFieldModel) {
            $position = $previousFieldModel == null ? "FIRST" : "AFTER {$previousFieldModel->getField()}";
            $this->pdo->query("
                ALTER TABLE {$currentFieldModel->getTable()}
                MODIFY $currentFieldModel $position
            ");
        });
        return new Unconformity($description, $instructions);
    }
}

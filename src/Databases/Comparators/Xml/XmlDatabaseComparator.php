<?php

namespace Squille\Databases\Comparators\Xml;

use DOMDocument;
use DOMElement;
use DOMException;
use Squille\Databases\Comparators\AbstractDatabaseComparator;
use Squille\Databases\Models\Database;
use Squille\Instruction;

class XmlDatabaseComparator extends AbstractDatabaseComparator
{
    private DOMDocument $document;

    public function __construct(DOMDocument $document)
    {
        $this->document = $document;
    }

    protected function getCollationChangeInstruction(Database $model): Instruction
    {
        $description = "xpath := /database@collation='{$model->getCollation()}'";
        return new Instruction(
            $description,
            function () use ($model) {
                $schemaElement = $this->retrieveOrCreateDatabaseElement();
                $schemaElement->setAttribute("collation", $model->getCollation());
            }
        );
    }

    /**
     * @throws DOMException
     */
    private function retrieveOrCreateDatabaseElement(): DOMElement
    {
        $list = $this->document->getElementsByTagName("database");

        if ($list->count() > 0) {
            $element = $list->item(0);
        } else {
            $element = $this->document->appendChild($this->document->createElement("database"));
        }

        return $element;
    }
}

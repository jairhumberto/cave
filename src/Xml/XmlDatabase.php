<?php

namespace Squille\Cave\Xml;

use DOMDocument;
use DOMElement;
use DOMException;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractDatabaseModel;
use Squille\Cave\Models\IDatabaseModel;
use Squille\Cave\Unconformity;

class XmlDatabase extends AbstractDatabaseModel
{
    /**
     * @var DOMElement
     */
    private $databaseElement;

    private $collation;
    private $tables;

    /**
     * @throws DOMException
     */
    public function __construct(DOMDocument $document)
    {
        $this->databaseElement = $this->retrieveOrCreateDatabaseElement($document);
        $this->collation = $this->databaseElement->getAttribute("collation");
        $this->tables = new XmlTablesList($this->databaseElement);
    }

    /**
     * @throws DOMException
     */
    private function retrieveOrCreateDatabaseElement(DOMDocument $document)
    {
        $databasesNodeList = $document->getElementsByTagName("database");
        $databaseElement = $databasesNodeList->item(0);
        if ($databaseElement == null) {
            $databaseElement = $document->appendChild($document->createElement("database"));
        }
        return $databaseElement;
    }

    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param string $filename
     */
    public function save($filename)
    {
        $this->databaseElement->ownerDocument->save($filename);
    }

    protected function collationUnconformity(IDatabaseModel $databaseModel)
    {
        $description = "Database collate ({$this->getCollation()}) differs from the model ({$databaseModel->getCollation()})";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($databaseModel) {
            $this->databaseElement->setAttribute("collation", $databaseModel->getCollation());
        });
        return new Unconformity($description, $instructions);
    }

    public function getCollation()
    {
        return $this->collation;
    }
}

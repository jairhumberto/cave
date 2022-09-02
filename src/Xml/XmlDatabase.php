<?php

namespace Squille\Cave\Xml;

use DOMDocument;
use DOMException;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractDatabaseModel;
use Squille\Cave\Models\IDatabaseModel;
use Squille\Cave\Unconformity;

class XmlDatabase extends AbstractDatabaseModel
{
    private $root;
    private $collation;
    private $tables;

    /**
     * @throws DOMException
     */
    public function __construct(DOMDocument $doc)
    {
        $this->root = $this->createRootElement($doc);
        $this->collation = $this->root->getAttribute("collation");
        $this->tables = new XmlTablesList($this->root);
    }

    /**
     * @throws DOMException
     */
    private function createRootElement(DOMDocument $doc)
    {
        if ($doc->firstChild == null) {
            $database = $doc->createElement("database");
            $doc->appendChild($database);
        }
        return $doc->firstChild;
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
        $this->root->ownerDocument->save($filename);
    }

    protected function collationUnconformity(IDatabaseModel $databaseModel)
    {
        $description = "Database collate ({$this->getCollation()}) differs from the model ({$databaseModel->getCollation()})";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($databaseModel) {
            $this->root->setAttribute("collation", $databaseModel->getCollation());
        });
        return new Unconformity($description, $instructions);
    }

    public function getCollation()
    {
        return $this->collation;
    }
}

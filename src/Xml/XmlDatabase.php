<?php

namespace Squille\Cave\Xml;

use DOMDocument;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IDatabaseModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class XmlDatabase implements IDatabaseModel
{
    private $root;
    private $collation;
    private $tables;

    public function __construct(DOMDocument $doc)
    {
        $this->root = $this->createRootElement($doc);
        $this->collation = $this->root->getAttribute("collation");
        $this->tables = new XmlTablesList($this->root);
    }

    private function createRootElement(DOMDocument $doc)
    {
        if ($doc->firstChild == null) {
            $database = $doc->createElement("database");
            $database->setAttribute("collation", "");
            $doc->appendChild($database);
        }
        return $doc->firstChild;
    }

    public function checkIntegrity(IDatabaseModel $databaseModel)
    {
        $unconformities = new UnconformitiesList();
        if ($this->getCollation() != $databaseModel->getCollation()) {
            $unconformities->add($this->collateUnconformity($databaseModel));
        }
        return $unconformities->merge($this->getTables()->checkIntegrity($databaseModel->getTables()));
    }

    public function getCollation()
    {
        return $this->collation;
    }

    private function collateUnconformity(IDatabaseModel $model)
    {
        $description = "Database collate ({$this->getCollation()}) differs from the model ({$model->getCollation()})";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->root->setAttribute("collation", $model->getCollation());
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @param string $filename
     */
    public function save($filename)
    {
        $this->root->ownerDocument->save($filename);
    }

    public function getTables()
    {
        return $this->tables;
    }
}

<?php

namespace Squille\Cave\Xml;

use DOMDocument;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IDatabaseModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class XmlDatabase implements IDatabaseModel
{
    private $xml;
    private $collation;

    public function __construct(DOMDocument $xml)
    {
        $this->xml = $xml;
        $this->initComponents();
    }

    private function initComponents()
    {
        $this->createRootElement();
        $this->retrieveCollation();
    }

    private function createRootElement()
    {
        if ($this->xml->firstChild == null) {
            $database = $this->xml->createElement("database");
            $database->setAttribute("collation", "");
            $this->xml->appendChild($database);
        }
    }

    private function retrieveCollation()
    {
        $this->collation = $this->xml->firstChild->getAttribute("collation");
    }

    public function checkIntegrity(IDatabaseModel $databaseModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getCollation() != $databaseModel->getCollation()) {
            $unconformities->add($this->collateUnconformity($databaseModel));
        }

        return $unconformities;
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
            $this->xml->firstChild->setAttribute("collation", $model->getCollation());
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @param string $filename
     */
    public function save($filename)
    {
        $this->xml->save($filename);
    }

    public function getTables()
    {
        // TODO: Implement getTables() method.
    }
}

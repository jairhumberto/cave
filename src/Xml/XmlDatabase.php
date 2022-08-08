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
    private $charset;
    private $collation;

    public function __construct(DOMDocument $xml)
    {
        $this->xml = $xml;
        $this->initProperties();
    }

    private function initProperties()
    {
        $this->createRootElement();
        $this->retrieveCharset();
        $this->retrieveCollation();
    }

    private function createRootElement()
    {
        if (is_null($this->xml->firstChild)) {
            $database = $this->xml->createElement("database");
            $database->setAttribute("charset", "");
            $database->setAttribute("collation", "");
            $this->xml->appendChild($database);
        }
    }

    private function retrieveCharset()
    {
        $this->charset = $this->xml->firstChild->getAttribute("charset");
    }

    private function retrieveCollation()
    {
        $this->collation = $this->xml->firstChild->getAttribute("collation");
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IDatabaseModel $model)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getCharset() != $model->getCharset()) {
            $unconformities->add($this->addCharsetUnconformity($model));
        }

        if ($this->getCollation() != $model->getCollation()) {
            $unconformities->add($this->addCollateUnconformity($model));
        }

        return $unconformities;
    }

    /**
     * @inheritDoc
     */
    public function getCharset()
    {
        return $this->charset;
    }

    private function addCharsetUnconformity(IDatabaseModel $model)
    {
        $description = "Database charset ({$this->getCharset()}) differs from the model ({$model->getCharset()})";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->xml->firstChild->setAttribute("charset", $model->getCharset());
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @inheritDoc
     */
    public function getCollation()
    {
        return $this->collation;
    }

    private function addCollateUnconformity(IDatabaseModel $model)
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
}

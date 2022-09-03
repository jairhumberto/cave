<?php

namespace Squille\Cave\Xml;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractIndexModel;
use Squille\Cave\Models\IIndexModel;
use Squille\Cave\Unconformity;

class XmlIndex extends AbstractIndexModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->pdo = $pdo;
        $this->table = $partialConstraints[0]->getTable();
        parent::__construct($partialConstraints);
    }

    public function getTable()
    {
        return $this->table;
    }

    protected function incompatibleIndexUnconformity(IIndexModel $indexModel)
    {
        $description = "alter table {$this->getTable()} drop index {$this->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () {
            $this->pdo->query("ALTER TABLE {$this->getTable()} DROP INDEX `{$this->getName()}`");
        });
        $instructions->add(function () use ($indexModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $indexModel");
        });
        return new Unconformity($description, $instructions);
    }
}

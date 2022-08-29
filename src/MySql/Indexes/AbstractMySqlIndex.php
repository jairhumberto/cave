<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IIndexModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractMySqlIndex extends ArrayList implements IIndexModel
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

    public function checkIntegrity(IIndexModel $indexModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->partialIndexesIncompatible($indexModel)) {
            $unconformities->add($this->incompatibleIndexUnconformity($indexModel));
        }

        return $unconformities;
    }

    private function partialIndexesIncompatible(IIndexModel $indexModel)
    {
        return $this->count() != $indexModel->count()
            || $this->indexesPartsMissing($indexModel)
            || $this->indexesPartsExceeding($indexModel);
    }

    private function indexesPartsMissing(IIndexModel $indexModel)
    {
        foreach ($indexModel as $key => $indexPartModel) {
            $currentIndexPart = $this->get($key);
            if (!$currentIndexPart->equals($indexPartModel)) {
                return true;
            }
        }
        return false;
    }

    private function indexesPartsExceeding(IIndexModel $indexModel)
    {
        foreach ($this as $key => $indexPart) {
            $currentIndexPartModel = $indexModel->get($key);
            if (!$currentIndexPartModel->equals($indexPart)) {
                return true;
            }
        }
        return false;
    }

    private function incompatibleIndexUnconformity(IIndexModel $indexModel)
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

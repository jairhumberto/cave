<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IIndexesListModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;

class MySqlIndexesList extends ArrayList implements IIndexesListModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, MySqlTable $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        parent::__construct($this->retrieveKeys());
    }

    private function retrieveKeys()
    {
        try {
            $stm = $this->pdo->query("SHOW KEYS IN {$this->table->getName()}");
            return $this->groupKeys($stm->fetchAll(PDO::FETCH_CLASS, MySqlKeyPart::class, [$this->pdo]) ?: []);
        } finally {
            if ($stm instanceof PDOStatement) {
                $stm->closeCursor();
            }
        }
    }

    private function groupKeys(array $keyParts)
    {
        $keys = [];
        $groups = $this->groupKeyParts($keyParts);
        foreach ($groups as $group) {
            $keys[] = MySqlIndexFactory::createInstance($this->pdo, $group);
        }
        return $keys;
    }

    private function groupKeyParts(array $keyParts)
    {
        $groups = [];
        foreach ($keyParts as $part) {
            if (!array_key_exists($part->getKeyName(), $groups)) {
                $groups[$part->getKeyName()] = [];
            }
            $groups[$part->getKeyName()][] = $part;
        }
        return $groups;
    }

    public function checkIntegrity(IIndexesListModel $indexesListModel)
    {
        return new UnconformitiesList();
//        return $this->missingKeysUnconformities($indexesListModel)
//            ->merge($this->generalKeysUnconformities($indexesListModel));
    }

//    private function missingKeysUnconformities(IIndexesListModel $keysListModel)
//    {
//        $unconformities = new UnconformitiesList();
//        foreach ($keysListModel as $keyModel) {
//            $callback = function ($item) use ($keyModel) {
//                return $item->getKeyName() == $keyModel->getKeyName();
//            };
//
//            $keyFound = $this->search($callback);
//
//            if ($keyFound == null) {
//                $unconformities->add($this->missingKeyUnconformity($keyModel));
//            }
//        }
//        return $unconformities;
//    }
//
//    private function missingKeyUnconformity(IIndexModel $keyModel)
//    {
//        $description = "create table {$keyModel->getKeyName()}";
//        $instructions = new InstructionsList();
//        $instructions->add(function () use ($keyModel) {
//            $tblName = $keyModel->getName();
//            $createDefinitions = $keyModel->getFields()->merge($keyModel->getKeys());
//            $tableOptions = $this->getTableOptions($keyModel);
//            $this->pdo->query("
//                CREATE TABLE $tblName
//                ($createDefinitions) $tableOptions
//            ");
//        });
//        return new Unconformity($description, $instructions);
//    }
//
//    private function generalKeysUnconformities(IIndexesListModel $keysListModel)
//    {
//        $unconformities = new UnconformitiesList();
//        foreach ($this as $key) {
//            $callback = function ($item) use ($key) {
//                return $item->getKeyName() == $key->getKeyName();
//            };
//
//            $exceedingKeyFound = $keysListModel->search($callback);
//
//            if ($exceedingKeyFound == null) {
//                $unconformities->add($this->exceedingKeyUnconformity($key));
//            } else {
//                $unconformities->merge($key->checkIntegrity($exceedingKeyFound));
//            }
//        }
//        return $unconformities;
//    }
//
//    private function exceedingKeyUnconformity(IIndexModel $table)
//    {
//        $description = "drop table {$table->getName()}";
//        $instructions = new InstructionsList();
//        $instructions->add(function () use ($table) {
//            $this->pdo->query("DROP TABLE {$table->getName()}");
//        });
//        return new Unconformity($description, $instructions);
//    }
}

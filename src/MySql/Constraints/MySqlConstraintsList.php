<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IConstraintsListModel;
use Squille\Cave\MySql\MySqlKeyPart;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;

class MySqlConstraintsList extends ArrayList implements IConstraintsListModel
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
            $keys[] = MySqlConstraintFactory::createInstance($this->pdo, $group);
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

    public function checkIntegrity(IConstraintsListModel $constraintsListModel)
    {
        return new UnconformitiesList();
    }
}

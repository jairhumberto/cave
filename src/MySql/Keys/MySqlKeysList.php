<?php

namespace Squille\Cave\MySql\Keys;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IKeysListModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\UnconformitiesList;

class MySqlKeysList extends ArrayList implements IKeysListModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, ITableModel $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        parent::__construct($this->retrieveKeys());
    }

    private function retrieveKeys()
    {
        try {
            $result = $this->pdo->query("SHOW KEYS IN `{$this->table->getName()}`");
            return $this->groupKeys($result->fetchAll(PDO::FETCH_CLASS, MySqlKeyPart::class, [$this->pdo]) ?: []);
        } finally {
            if ($result instanceof PDOStatement) {
                $result->closeCursor();
            }
        }
    }

    private function groupKeys(array $keyParts)
    {
        $keys = [];
        $groups = $this->groupKeyParts($keyParts);
        foreach ($groups as $group) {
            $keys[] = MySqlKeyFactory::createInstance($this->pdo, $group);
        }
        return $keys;
    }

    private function groupKeyParts(array $keyParts)
    {
        $groups = [];
        foreach($keyParts as $part) {
            if (!array_key_exists($part->getKeyName(), $groups)) {
                $groups[$part->getKeyName()] = [];
            }
            $groups[$part->getKeyName()][] = $part;
        }
        return $groups;
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IKeysListModel $model)
    {
        return new UnconformitiesList();
    }
}

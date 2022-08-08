<?php

namespace Squille\Cave\MySql;

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
            return $result->fetchAll(PDO::FETCH_CLASS, MySqlKey::class, [$this->pdo]) ?: [];
        } finally {
            if ($result instanceof PDOStatement) {
                $result->closeCursor();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IKeysListModel $model)
    {
        return new UnconformitiesList();
    }
}

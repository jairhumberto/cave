<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractDatabaseModel;
use Squille\Cave\Models\DatabaseModelInterface;
use Squille\Cave\Unconformity;

class MySqlDatabase extends AbstractDatabaseModel
{
    private $pdo;
    private $collation;
    private $tables;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->collation = $this->retrieveCollation();
        $this->tables = new MySqlTablesList($pdo);
    }

    private function retrieveCollation()
    {
        try {
            $stm = $this->pdo->query("SELECT @@collation_database");
            return $stm->fetchColumn();
        } finally {
            if ($stm instanceof PDOStatement) {
                $stm->closeCursor();
            }
        }
    }

    public function getTables()
    {
        return $this->tables;
    }

    protected function collationUnconformity(DatabaseModelInterface $databaseModel)
    {
        $description = "alter database collate {{$this->getCollation()} -> {$databaseModel->getCollation()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($databaseModel) {
            $this->pdo->query("ALTER DATABASE COLLATE `{$databaseModel->getCollation()}`");
        });
        return new Unconformity($description, $instructions);
    }

    public function getCollation()
    {
        return $this->collation;
    }
}

<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IDatabaseModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlDatabase implements IDatabaseModel
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

    public function checkIntegrity(IDatabaseModel $databaseModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getCollation() != $databaseModel->getCollation()) {
            $unconformities->add($this->collationUnconformity($databaseModel));
        }

        return $unconformities->merge($this->getTables()->checkIntegrity($databaseModel->getTables()));
    }

    public function getCollation()
    {
        return $this->collation;
    }

    private function collationUnconformity(IDatabaseModel $databaseModel)
    {
        $description = "alter database collate {{$this->getCollation()} -> {$databaseModel->getCollation()}}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($databaseModel) {
            $this->pdo->query("ALTER DATABASE COLLATE {$databaseModel->getCollation()}");
        });
        return new Unconformity($description, $instructions);
    }

    public function getTables()
    {
        return $this->tables;
    }
}

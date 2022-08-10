<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IDatabaseModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlDatabase implements IDatabaseModel
{
    private $pdo;
    private $charset;
    private $collation;
    private $tables;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->init();
    }

    private function init()
    {
        $this->retrieveCharset();
        $this->retrieveCollation();
        $this->retrieveTables();
    }

    private function retrieveCharset()
    {
        $result = $this->pdo->query("SHOW VARIABLES LIKE 'character_set_database'");
        $this->charset = $result->fetchObject()->Value;
        $result->closeCursor();
    }

    private function retrieveCollation()
    {
        $result = $this->pdo->query("SHOW VARIABLES LIKE 'collation_database'");
        $this->collation = $result->fetchObject()->Value;
        $result->closeCursor();
    }

    private function retrieveTables()
    {
        $this->tables = new MySqlTablesList($this->pdo);
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IDatabaseModel $model)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getCharset() != $model->getCharset()) {
            $unconformities->add($this->charsetUnconformity($model));
        }

        if ($this->getCollation() != $model->getCollation()) {
            $unconformities->add($this->collateUnconformity($model));
        }

        return $unconformities->merge($this->getTables()->checkIntegrity($model->getTables()));
    }

    /**
     * @inheritDoc
     */
    public function getCharset()
    {
        return $this->charset;
    }

    private function charsetUnconformity(IDatabaseModel $model)
    {
        $description = "alter database character set {`{$this->getCharset()}` -> `{$model->getCharset()}`}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("ALTER DATABASE CHARACTER SET `{$model->getCharset()}`");
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

    private function collateUnconformity(IDatabaseModel $model)
    {
        $description = "alter database collate {`{$this->getCollation()}` -> `{$model->getCollation()}`}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("ALTER DATABASE COLLATE `{$model->getCollation()}`");
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @inheritDoc
     */
    public function getTables()
    {
        return $this->tables;
    }
}

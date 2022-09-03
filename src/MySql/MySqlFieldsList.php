<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractFieldModel;
use Squille\Cave\Models\AbstractFieldsListModel;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\Unconformity;

class MySqlFieldsList extends AbstractFieldsListModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, MySqlTable $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        parent::__construct($this->retrieveFields());
    }

    private function retrieveFields()
    {
        try {
            $result = $this->pdo->query("SHOW FULL FIELDS IN {$this->getTable()}");
            return $result->fetchAll(PDO::FETCH_CLASS, MySqlField::class, [$this->pdo, $this->table]) ?: [];
        } finally {
            if ($result instanceof PDOStatement) {
                $result->closeCursor();
            }
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    protected function missingFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
    {
        $description = "alter table {$currentFieldModel->getTable()} add {$currentFieldModel->getField()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentFieldModel, $previousFieldModel) {
            $position = $previousFieldModel == null ? "FIRST" : "AFTER {$previousFieldModel->getField()}";
            $this->pdo->query("
                ALTER TABLE {$currentFieldModel->getTable()}
                ADD COLUMN $currentFieldModel $position
            ");
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingFieldUnconformity(AbstractFieldModel $mySqlField)
    {
        $description = "alter table {$mySqlField->getTable()} drop column {$mySqlField->getField()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlField) {
            $this->pdo->query("
                ALTER TABLE {$mySqlField->getTable()}
                DROP COLUMN {$mySqlField->getField()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    protected function orderFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
    {
        $description = "alter table {$currentFieldModel->getTable()} modify {$currentFieldModel->getField()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentFieldModel, $previousFieldModel) {
            $position = $previousFieldModel == null ? "FIRST" : "AFTER {$previousFieldModel->getField()}";
            $this->pdo->query("
                ALTER TABLE {$currentFieldModel->getTable()}
                MODIFY $currentFieldModel $position
            ");
        });
        return new Unconformity($description, $instructions);
    }
}

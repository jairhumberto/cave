<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\Models\IFieldsListModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlFieldsList extends ArrayList implements IFieldsListModel
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

    public function checkIntegrity(IFieldsListModel $fieldsListModel)
    {
        return $this->missingFieldsUnconformities($fieldsListModel)
            ->merge($this->generalFieldsUnconformities($fieldsListModel))
            ->merge($this->orderFieldsUnconformities($fieldsListModel));
    }

    private function missingFieldsUnconformities(IFieldsListModel $fieldsListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($fieldsListModel as $key => $fieldModel) {
            $callback = function ($item) use ($fieldModel) {
                return $item->getField() == $fieldModel->getField();
            };

            $fieldFound = $this->search($callback);

            if ($fieldFound == null) {
                if ($key == 0) {
                    $previousFieldModel = null;
                } else {
                    $previousFieldModel = $fieldsListModel->get($key - 1);
                }
                $this->addField($fieldModel);
                $unconformities->add($this->missingFieldUnconformity($fieldModel, $previousFieldModel));
            }
        }
        return $unconformities;
    }

    private function addField(IFieldModel $currentFieldModel)
    {
        $this->add($currentFieldModel);
    }

    private function missingFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
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

    private function generalFieldsUnconformities(IFieldsListModel $fieldsListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $field) {
            $callback = function ($item) use ($field) {
                return $item->getField() == $field->getField();
            };

            $fieldModelFound = $fieldsListModel->search($callback);

            if ($fieldModelFound == null) {
                $unconformities->add($this->exceedingFieldUnconformity($field));
            } else {
                $unconformities->merge($field->checkIntegrity($fieldModelFound));
            }
        }
        return $unconformities;
    }

    private function exceedingFieldUnconformity(MySqlField $mySqlField)
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

    private function orderFieldsUnconformities(IFieldsListModel $fieldsListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($fieldsListModel as $key => $fieldModel) {
            $field = $this->get($key);
            if ($field->getField() != $fieldModel->getField()) {
                if ($key == 0) {
                    $previousFieldModel = null;
                } else {
                    $previousFieldModel = $fieldsListModel->get($key - 1);
                }
                $unconformities->add($this->orderFieldUnconformity($fieldModel, $previousFieldModel));
            }
        }
        return $unconformities;
    }

    private function orderFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
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

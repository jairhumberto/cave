<?php

namespace Squille\Cave\MySql;

use PDO;
use PDOStatement;
use Squille\Cave\ArrayList;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\Models\IFieldsListModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlFieldsList extends ArrayList implements IFieldsListModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, ITableModel $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        parent::__construct($this->retrieveFields());
    }

    private function retrieveFields()
    {
        try {
            $result = $this->pdo->query("SHOW FULL FIELDS IN `{$this->table->getName()}`");
            return $result->fetchAll(PDO::FETCH_CLASS, MySqlField::class, [$this->pdo, $this->table]) ?: [];
        } finally {
            if ($result instanceof PDOStatement) {
                $result->closeCursor();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IFieldsListModel $model)
    {
        return $this->missingFieldsUnconformities($model)
            ->merge($this->generalFieldsUnconformities($model))
            ->merge($this->orderFieldsUnconformities($model));
    }

    private function missingFieldsUnconformities(IFieldsListModel $fieldListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($fieldListModel as $key => $fieldModel) {
            $callback = function ($item) use ($fieldModel) {
                return $item->getField() == $fieldModel->getField();
            };

            $fieldFound = $this->search($callback);

            if ($fieldFound == null) {
                if ($key == 0) {
                    $previousFieldModel = null;
                } else {
                    $previousFieldModel = $fieldListModel->get($key - 1);
                }
                $unconformities->add($this->missingFieldUnconformity($fieldModel, $previousFieldModel));
            }
        }
        return $unconformities;
    }

    private function missingFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
    {
        $description = "alter table `{$this->table->getName()}` add `{$currentFieldModel->getField()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentFieldModel, $previousFieldModel) {
            $position = $previousFieldModel == null ? "FIRST" : "AFTER {$previousFieldModel->getField()}";
            $this->pdo->query("
                ALTER TABLE `{$this->table->getName()}`
                ADD COLUMN $currentFieldModel $position
            ");
        });
        return new Unconformity($description, $instructions);
    }

    private function generalFieldsUnconformities(IFieldsListModel $model)
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $field) {
            $callback = function ($item) use ($field) {
                return $item->getField() == $field->getField();
            };

            $exceedingFieldModel = $model->search($callback);

            if ($exceedingFieldModel == null) {
                $unconformities->add($this->exceedingFieldUnconformity($field));
            } else {
                $unconformities->merge($field->checkIntegrity($exceedingFieldModel));
            }
        }
        return $unconformities;
    }

    private function exceedingFieldUnconformity(IFieldModel $field)
    {
        $description = "alter table `{$this->table->getName()}` drop column `{$field->getField()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($field) {
            $this->pdo->query("
                ALTER TABLE `{$this->table->getName()}`
                DROP COLUMN `{$field->getField()}`
            ");
        });
        return new Unconformity($description, $instructions);
    }

    private function orderFieldsUnconformities(IFieldsListModel $model)
    {
        $unconformities = new UnconformitiesList();
        foreach ($model as $key => $currentFieldModel) {
            $currentField = $this->get($key);
            if ($currentField->getField() != $currentFieldModel->getField()) {
                if ($key == 0) {
                    $previousFieldModel = null;
                } else {
                    $previousFieldModel = $model->get($key - 1);
                }
                $unconformities->add($this->orderFieldUnconformity($currentFieldModel, $previousFieldModel));
            }
        }
        return $unconformities;
    }

    private function orderFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel)
    {
        $description = "alter table `{$this->table->getName()}` modify `{$currentFieldModel->getField()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentFieldModel, $previousFieldModel) {
            $position = $previousFieldModel == null ? "FIRST" : "AFTER {$previousFieldModel->getField()}";
            $this->pdo->query("
                ALTER TABLE `{$this->table->getName()}`
                MODIFY $currentFieldModel $position
            ");
        });
        return new Unconformity($description, $instructions);
    }
}

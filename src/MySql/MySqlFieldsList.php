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

    private function missingFieldsUnconformities(IFieldsListModel $model)
    {
        $unconformities = new UnconformitiesList();
        foreach ($model as $key => $modelField) {
            $searchCallback = function (IFieldModel $item) use ($modelField) {
                return $item->getField() == $modelField->getField();
            };

            $field = $this->search($searchCallback);

            if (is_null($field)) {
                $unconformities->add($this->missingFieldUnconformity($modelField, $key > 0 ? $model->get($key - 1) : null));
            }
        }
        return $unconformities;
    }

    private function missingFieldUnconformity(IFieldModel $currentModelField, IFieldModel $previousModelField)
    {
        $description = "alter table `{$this->table->getName()}` add `{$currentModelField->getField()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentModelField, $previousModelField) {
            $position = is_null($previousModelField) ? "FIRST" : "AFTER {$previousModelField->getField()}";
            $this->pdo->query("
                ALTER TABLE `{$this->table->getName()}`
                ADD COLUMN $currentModelField $position
            ");
        });
        return new Unconformity($description, $instructions);
    }

    private function generalFieldsUnconformities(IFieldsListModel $model)
    {
        $unconformities = new UnconformitiesList();

        /** @var MySqlField $field */
        foreach ($this as $field) {
            $searchCallback = function (IFieldModel $item) use ($field) {
                return $item->getField() == $field->getField();
            };

            $fieldModel = $model->search($searchCallback);

            if (is_null($fieldModel)) {
                $unconformities->add($this->exceedingFieldUnconformity($field));
            } else {
                $unconformities->merge($field->checkIntegrity($fieldModel));
            }
        }

        return $unconformities;
    }

    private function exceedingFieldUnconformity(IFieldModel $field)
    {
        $description = "alter table `{$this->table->getName()}` drop column `{$field->getField()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($field) {
            $this->pdo->query("ALTER TABLE `{$this->table->getName()}` DROP COLUMN `{$field->getField()}`");
        });
        return new Unconformity($description, $instructions);
    }

    private function orderFieldsUnconformities(IFieldsListModel $model)
    {
        /**
         * @var IFieldModel $currentFieldModel
         * @var IFieldModel $previousFieldModel
         * @var IFieldModel $currentField
         */

        $unconformities = new UnconformitiesList();

        foreach ($model as $key => $currentFieldModel) {
            $currentField = $this->get($key);
            if ($currentField->getField() != $currentFieldModel->getField()) {
                $previousFieldModel = $key > 0 ? $model->get($key - 1) : null;
                $unconformities->add($this->orderFieldUnconformity($currentFieldModel, $previousFieldModel));
            }
        }

        return $unconformities;
    }

    private function orderFieldUnconformity(IFieldModel $currentModelField, IFieldModel $previousModelField)
    {
        $description = "alter table `{$this->table->getName()}` modify `{$currentModelField->getField()}`";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($currentModelField, $previousModelField) {
            $position = is_null($previousModelField) ? "FIRST" : "AFTER {$previousModelField->getField()}";
            $this->pdo->query("
                ALTER TABLE `{$this->table->getName()}`
                MODIFY $currentModelField $position
            ");
        });
        return new Unconformity($description, $instructions);
    }
}

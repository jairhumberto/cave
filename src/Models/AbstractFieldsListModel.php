<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractFieldsListModel extends ArrayList implements FieldsListModelInterface
{
    public function checkIntegrity(FieldsListModelInterface $fieldsListModel): UnconformitiesList
    {
        return $this->missingFieldsUnconformities($fieldsListModel)
            ->merge($this->generalFieldsUnconformities($fieldsListModel))
            ->merge($this->orderFieldsUnconformities($fieldsListModel));
    }

    private function missingFieldsUnconformities(FieldsListModelInterface $fieldsListModel): UnconformitiesList
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

    private function addField(FieldModelInterface $currentFieldModel): void
    {
        $this->add($currentFieldModel);
    }

    abstract protected function missingFieldUnconformity(FieldModelInterface $currentFieldModel, FieldModelInterface $previousFieldModel): Unconformity;

    private function generalFieldsUnconformities(FieldsListModelInterface $fieldsListModel): UnconformitiesList
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

    abstract protected function exceedingFieldUnconformity(AbstractFieldModel $mySqlField): Unconformity;

    private function orderFieldsUnconformities(FieldsListModelInterface $fieldsListModel): UnconformitiesList
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

    abstract protected function orderFieldUnconformity(FieldModelInterface $currentFieldModel, FieldModelInterface $previousFieldModel): Unconformity;
}

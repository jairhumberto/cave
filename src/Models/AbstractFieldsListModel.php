<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractFieldsListModel extends ArrayList implements FieldsListModelInterface
{
    public function checkIntegrity(FieldsListModelInterface $fieldsListModel)
    {
        return $this->missingFieldsUnconformities($fieldsListModel)
            ->merge($this->generalFieldsUnconformities($fieldsListModel))
            ->merge($this->orderFieldsUnconformities($fieldsListModel));
    }

    private function missingFieldsUnconformities(FieldsListModelInterface $fieldsListModel)
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

    private function addField(FieldModelInterface $currentFieldModel)
    {
        $this->add($currentFieldModel);
    }

    /**
     * @param FieldModelInterface $currentFieldModel
     * @param FieldModelInterface $previousFieldModel
     * @return Unconformity
     */
    abstract protected function missingFieldUnconformity(FieldModelInterface $currentFieldModel, FieldModelInterface $previousFieldModel);

    private function generalFieldsUnconformities(FieldsListModelInterface $fieldsListModel)
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

    /**
     * @param AbstractFieldModel $mySqlField
     * @return Unconformity
     */
    abstract protected function exceedingFieldUnconformity(AbstractFieldModel $mySqlField);

    private function orderFieldsUnconformities(FieldsListModelInterface $fieldsListModel)
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

    /**
     * @param FieldModelInterface $currentFieldModel
     * @param FieldModelInterface $previousFieldModel
     * @return Unconformity
     */
    abstract protected function orderFieldUnconformity(FieldModelInterface $currentFieldModel, FieldModelInterface $previousFieldModel);
}

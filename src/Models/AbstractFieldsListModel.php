<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractFieldsListModel extends ArrayList implements IFieldsListModel
{
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

    /**
     * @param IFieldModel $currentFieldModel
     * @param IFieldModel $previousFieldModel
     * @return Unconformity
     */
    abstract protected function missingFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel);

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

    /**
     * @param AbstractFieldModel $mySqlField
     * @return Unconformity
     */
    abstract protected function exceedingFieldUnconformity(AbstractFieldModel $mySqlField);

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

    /**
     * @param IFieldModel $currentFieldModel
     * @param IFieldModel $previousFieldModel
     * @return Unconformity
     */
    abstract protected function orderFieldUnconformity(IFieldModel $currentFieldModel, IFieldModel $previousFieldModel);
}

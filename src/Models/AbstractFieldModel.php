<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractFieldModel implements IFieldModel
{
    public function checkIntegrity(IFieldModel $fieldModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getType() != $fieldModel->getType()) {
            $unconformities->add($this->typeUnconformity($fieldModel));
        }

        if ($this->getCollation() != $fieldModel->getCollation()) {
            $unconformities->add($this->collationUnconformity($fieldModel));
        }

        if ($this->getNull() != $fieldModel->getNull()) {
            $unconformities->add($this->nullUnconformity($fieldModel));
        }

        if ($this->getDefault() != $fieldModel->getDefault()) {
            $unconformities->add($this->defaultUnconformity($fieldModel));
        }

        if ($this->getExtra() != $fieldModel->getExtra()) {
            $unconformities->add($this->extraUnconformity($fieldModel));
        }

        if ($this->getComment() != $fieldModel->getComment()) {
            $unconformities->add($this->commentUnconformity($fieldModel));
        }

        if ($unconformities->any()) {
            $unconformities->add($this->definitionUnconformity($fieldModel));
        }

        return $unconformities;
    }

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function typeUnconformity(IFieldModel $fieldModel);

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function collationUnconformity(IFieldModel $fieldModel);

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function nullUnconformity(IFieldModel $fieldModel);

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function defaultUnconformity(IFieldModel $fieldModel);

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function extraUnconformity(IFieldModel $fieldModel);

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function commentUnconformity(IFieldModel $fieldModel);

    /**
     * @param IFieldModel $fieldModel
     * @return Unconformity
     */
    abstract protected function definitionUnconformity(IFieldModel $fieldModel);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

abstract class AbstractFieldModel implements FieldModelInterface
{
    public function checkIntegrity(FieldModelInterface $fieldModel)
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
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function typeUnconformity(FieldModelInterface $fieldModel);

    /**
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function collationUnconformity(FieldModelInterface $fieldModel);

    /**
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function nullUnconformity(FieldModelInterface $fieldModel);

    /**
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function defaultUnconformity(FieldModelInterface $fieldModel);

    /**
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function extraUnconformity(FieldModelInterface $fieldModel);

    /**
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function commentUnconformity(FieldModelInterface $fieldModel);

    /**
     * @param FieldModelInterface $fieldModel
     * @return Unconformity
     */
    abstract protected function definitionUnconformity(FieldModelInterface $fieldModel);
}

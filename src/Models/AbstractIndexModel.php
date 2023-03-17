<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;

abstract class AbstractIndexModel extends ArrayList implements IndexModelInterface
{
    public function checkIntegrity(IndexModelInterface $indexModel)
    {
        $unconformities = new UnconformitiesList();
        if ($this->partialIndexesIncompatible($indexModel)) {
            $unconformities->add($this->incompatibleIndexUnconformity($indexModel));
        }
        return $unconformities;
    }

    private function partialIndexesIncompatible(IndexModelInterface $indexModel)
    {
        return $this->count() != $indexModel->count()
            || $this->indexesPartsMissing($indexModel)
            || $this->indexesPartsExceeding($indexModel);
    }

    private function indexesPartsMissing(IndexModelInterface $indexModel)
    {
        foreach ($indexModel as $key => $indexPartModel) {
            $currentIndexPart = $this->get($key);
            if (!$currentIndexPart->equals($indexPartModel)) {
                return true;
            }
        }
        return false;
    }

    private function indexesPartsExceeding(IndexModelInterface $indexModel)
    {
        foreach ($this as $key => $indexPart) {
            $currentIndexPartModel = $indexModel->get($key);
            if (!$currentIndexPartModel->equals($indexPart)) {
                return true;
            }
        }
        return false;
    }

    abstract protected function incompatibleIndexUnconformity(IndexModelInterface $indexModel);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\ArrayList;
use Squille\Cave\UnconformitiesList;

abstract class AbstractIndexesListModel extends ArrayList implements IndexesListModelInterface
{
    public function checkIntegrity(IndexesListModelInterface $indexesListModel)
    {
        return $this->missingIndexesUnconformities($indexesListModel)
            ->merge($this->generalIndexesUnconformities($indexesListModel));
    }

    private function missingIndexesUnconformities(IndexesListModelInterface $indexesListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($indexesListModel as $indexModel) {
            $callback = function ($item) use ($indexModel) {
                return $item->getName() == $indexModel->getName();
            };
            $indexFound = $this->search($callback);
            if ($indexFound == null) {
                $unconformities->add($this->missingIndexUnconformity($indexModel));
            }
        }
        return $unconformities;
    }

    abstract protected function missingIndexUnconformity(IndexModelInterface $indexModel);

    private function generalIndexesUnconformities(IndexesListModelInterface $indexesListModel)
    {
        $unconformities = new UnconformitiesList();
        foreach ($this as $index) {
            $callback = function ($item) use ($index) {
                return $item->getName() == $index->getName();
            };
            $indexModelFound = $indexesListModel->search($callback);
            if ($indexModelFound == null) {
                $unconformities->add($this->exceedingIndexUnconformity($index));
            } else {
                $unconformities->merge($index->checkIntegrity($indexModelFound));
            }
        }
        return $unconformities;
    }

    abstract protected function exceedingIndexUnconformity(AbstractIndexModel $mySqlIndex);
}

<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface TableModelInterface
{
    public function getName(): string;
    public function getEngine(): string;
    public function getRowFormat(): string;
    public function getCollation(): string;
    public function getChecksum(): string;
    public function getFields(): FieldsListModelInterface;
    public function getConstraints(): ConstraintsListModelInterface;
    public function getIndexes(): IndexesListModelInterface;
    public function checkIntegrity(TableModelInterface $tableModel): UnconformitiesList;
}

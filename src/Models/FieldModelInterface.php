<?php

namespace Squille\Cave\Models;

use Squille\Cave\UnconformitiesList;

interface FieldModelInterface
{
    public function getTable(): TableModelInterface;
    public function getField(): string;
    public function getType(): string;
    public function getCollation(): string;
    public function getNull(): string;
    public function getKey(): string;
    public function getDefault(): string;
    public function getExtra(): string;
    public function getComment(): string;
    public function checkIntegrity(FieldModelInterface $fieldModel): UnconformitiesList;
}

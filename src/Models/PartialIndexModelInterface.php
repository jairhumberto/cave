<?php

namespace Squille\Cave\Models;

interface PartialIndexModelInterface
{
    public function getColumn(): string;
    public function getName(): string;
    public function getType(): string;
    public function getTable(): string;
    public function equals(PartialIndexModelInterface $partialIndexModel): bool;
}

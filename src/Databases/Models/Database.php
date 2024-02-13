<?php

namespace Squille\Databases\Models;

class Database
{
    private string $collation;

    public function getCollation(): string
    {
        return $this->collation;
    }

    public function setCollation(string $collation): void
    {
        $this->collation = $collation;
    }
}

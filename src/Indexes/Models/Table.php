<?php

namespace Squille\Tables\Models;

class Table
{
    private string $name;
    private string $engine;
    private string $rowFormat;
    private string $collation;
    private string $checksum;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function setEngine(string $engine): void
    {
        $this->engine = $engine;
    }

    public function getRowFormat(): string
    {
        return $this->rowFormat;
    }

    public function setRowFormat(string $rowFormat): void
    {
        $this->rowFormat = $rowFormat;
    }

    public function getCollation(): string
    {
        return $this->collation;
    }

    public function setCollation(string $collation): void
    {
        $this->collation = $collation;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }
}

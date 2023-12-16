<?php

namespace Squille\Cave;

class Unconformity
{
    private $description;
    private $instructions;

    public function __construct($description, InstructionsList $instructions = null)
    {
        $this->description = $description;
        $this->instructions = $instructions ?: new InstructionsList();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function fix(): void
    {
        foreach ($this->instructions as $instruction) {
            $instruction();
        }
    }
}

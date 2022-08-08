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

    public function getDescription()
    {
        return $this->description;
    }

    public function fix()
    {
        foreach ($this->instructions as $instruction) {
            $instruction();
        }
    }
}

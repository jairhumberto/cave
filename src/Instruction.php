<?php

namespace Squille;

class Instruction
{
    private $action;
    private string $description;

    public function __construct(string $description, callable $action)
    {
        $this->action = $action;
        $this->description = $description;
    }

    public function getTextRepresentation(): string
    {
        return $this->description;
    }

    public function execute(): void
    {
        call_user_func($this->action);
    }
}

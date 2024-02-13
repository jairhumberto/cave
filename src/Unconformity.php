<?php

namespace Squille;

class Unconformity
{
    private string $description;
    private ArrayList $instructions;

    public function __construct(string $description, ArrayList $instructions)
    {
        $this->description = $description;
        $this->instructions = $instructions;
    }

    public function fix(bool $verbose = false, bool $dryRun = false): void
    {
        printf("== %s ==\n", $this->description);

        if ($this->shouldRunInstructions($verbose, $dryRun)) {
            $this->runInstructions($verbose, $dryRun);
        }
    }

    private function shouldRunInstructions(bool $verbose, bool $dryRun): bool
    {
        return $verbose || $dryRun;
    }

    private function runInstructions(bool $verbose, bool $dryRun): void
    {
        /** @var Instruction $instruction */
        foreach ($this->instructions as $instruction) {
            if ($verbose) {
                printf("\t%s\n", $instruction->getTextRepresentation());
            }
            if (!$dryRun) {
                $instruction->execute();
            }
        }
    }
}

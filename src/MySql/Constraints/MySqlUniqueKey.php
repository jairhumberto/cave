<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IConstraintModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlUniqueKey extends AbstractMySqlConstraint
{
    private $name;
    private $type;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->name = $partialConstraints[0]->getName();
        $this->type = $partialConstraints[0]->getType();

        parent::__construct($pdo, $partialConstraints);
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return sprintf("UNIQUE KEY %s USING %s (%s)", $this->name, $this->type, parent::__toString());
    }

    public function dropCommand()
    {
        return "DROP KEY {$this->getName()}";
    }
}

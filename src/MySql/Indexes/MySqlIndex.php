<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;

class MySqlIndex extends AbstractMySqlIndex
{
    private $name;
    private $type;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->name = $partialConstraints[0]->getName();
        $this->type = $partialConstraints[0]->getType();
        parent::__construct($pdo, $partialConstraints);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return sprintf("KEY %s USING %s (%s)", $this->name, $this->type, parent::__toString());
    }
}

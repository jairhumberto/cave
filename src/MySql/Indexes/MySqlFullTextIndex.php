<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;

class MySqlFullTextIndex extends AbstractMySqlIndex
{
    private $name;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->name = $partialConstraints[0]->getName();
        parent::__construct($pdo, $partialConstraints);
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return sprintf("FULLTEXT KEY %s (%s)", $this->name, parent::__toString());
    }
}

<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\Models\IIndexModel;
use Squille\Cave\UnconformitiesList;

class MySqlFullTextIndex extends AbstractMySqlIndex
{
    private $pdo;
    private $name;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->pdo = $pdo;
        $this->name = $partialConstraints[0]->getName();
        parent::__construct($partialConstraints);
    }

    public function checkIntegrity(IIndexModel $indexModel)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return sprintf("FULLTEXT KEY %s (%s)", $this->name, parent::__toString());
    }
}

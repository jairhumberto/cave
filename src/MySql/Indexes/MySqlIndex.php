<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\Models\IIndexModel;
use Squille\Cave\UnconformitiesList;

class MySqlIndex extends AbstractMySqlIndex
{
    private $pdo;
    private $name;
    private $type;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->pdo = $pdo;

        $this->name = $partialConstraints[0]->getName();
        $this->type = $partialConstraints[0]->getType();

        parent::__construct($partialConstraints);
    }

    public function checkIntegrity(IIndexModel $indexModel)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return sprintf("KEY %s USING %s (%s)", $this->name, $this->type, parent::__toString());
    }
}

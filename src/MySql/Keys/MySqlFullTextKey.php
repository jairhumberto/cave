<?php

namespace Squille\Cave\MySql\Keys;

use PDO;
use Squille\Cave\Models\IKeyModel;
use Squille\Cave\UnconformitiesList;

class MySqlFullTextKey extends AbstractMySqlKey
{
    private $pdo;
    private $name;

    public function __construct(PDO $pdo, array $keyParts)
    {
        $this->pdo = $pdo;
        $this->name = $keyParts[0]->getKeyName();
        parent::__construct($keyParts);
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IKeyModel $model)
    {
        return new UnconformitiesList();
    }

    public function __toString()
    {
        return sprintf("FULLTEXT KEY `%s` (%s)", $this->name, parent::__toString());
    }
}

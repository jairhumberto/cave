<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\Models\IKeyModel;
use Squille\Cave\UnconformitiesList;

class MySqlUniqueKey extends AbstractMySqlKey
{
    private $name;
    private $type;
    private $pdo;

    public function __construct(PDO $pdo, array $keyParts)
    {
        $this->pdo = $pdo;
        $this->name = $keyParts[0]->getKeyName();
        $this->type = $keyParts[0]->getIndexType();

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
        return sprintf("UNIQUE KEY `%s` USING %s (%s)", $this->name, $this->type, parent::__toString());
    }
}

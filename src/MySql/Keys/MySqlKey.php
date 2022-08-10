<?php

namespace Squille\Cave\MySql\Keys;

use PDO;
use Squille\Cave\Models\IKeyModel;
use Squille\Cave\UnconformitiesList;

class MySqlKey extends AbstractMySqlKey
{
    private $pdo;
    private $name;
    private $type;

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
        return sprintf("KEY `%s` USING %s (%s)", $this->name, $this->type, parent::__toString());
    }
}

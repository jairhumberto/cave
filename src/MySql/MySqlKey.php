<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IKeyModel;
use Squille\Cave\UnconformitiesList;

class MySqlKey extends ArrayList implements IKeyModel
{
    private $pdo;

    public function __construct(PDO $pdo, array $keyParts)
    {
        $this->pdo = $pdo;
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
        return sprintf("PRIMARY KEY (%s)", parent::__toString());
    }
}

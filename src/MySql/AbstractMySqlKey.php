<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\IKeyModel;
use Squille\Cave\UnconformitiesList;

abstract class AbstractMySqlKey extends ArrayList implements IKeyModel
{
    const PRIMARY_KEY = "PRIMARY";
}

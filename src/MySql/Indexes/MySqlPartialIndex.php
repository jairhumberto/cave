<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\Models\IPartialIndexModel;
use Squille\Cave\UnconformitiesList;

class MySqlPartialIndex implements IPartialIndexModel
{
    private $pdo;
    private $index_name;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public static function selectExpressions()
    {
        $allKeys = array_keys(get_class_vars(MySqlPartialIndex::class));
        $properties = array_filter($allKeys, function ($item) {
            return $item != "pdo";
        });
        return join(",", $properties);
    }

    public function checkIntegrity(IPartialIndexModel $partialIndexModel)
    {
        return new UnconformitiesList();
    }

    public function getIndexName()
    {
        return $this->index_name;
    }
}

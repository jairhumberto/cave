<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\Models\IPartialConstraintModel;
use Squille\Cave\UnconformitiesList;

class MySqlPartialConstraint implements IPartialConstraintModel
{
    private $pdo;
    private $constraint_name;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public static function selectExpressions()
    {
        $allKeys = array_keys(get_class_vars(MySqlPartialConstraint::class));
        $properties = array_filter($allKeys, function ($item) {
            return $item != "pdo";
        });
        return join(",", $properties);
    }

    public function checkIntegrity(IPartialConstraintModel $partialConstraintModel)
    {
        return new UnconformitiesList();
    }

    public function getConstraintName()
    {
        return $this->constraint_name;
    }
}

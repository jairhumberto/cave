<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\Models\IPartialConstraintModel;
use Squille\Cave\MySql\MySqlTable;
use Squille\Cave\UnconformitiesList;

class MySqlPartialConstraint implements IPartialConstraintModel
{
    private $pdo;
    private $table;
    private $column_name;
    private $index_name;
    private $index_type;

    public function __construct(PDO $pdo, MySqlTable $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * @return string
     */
    public static function selectExpressions()
    {
        $allKeys = array_keys(get_class_vars(MySqlPartialConstraint::class));
        $properties = array_filter($allKeys, function ($item) {
            return $item != "pdo" && $item != "table";
        });
        return join(",", $properties);
    }

    public function checkIntegrity(IPartialConstraintModel $partialConstraintModel)
    {
        return new UnconformitiesList();
    }

    public function getName()
    {
        return $this->index_name;
    }

    public function getType()
    {
        return $this->index_type;
    }

    public function __toString()
    {
        return $this->getColumn();
    }

    public function getColumn()
    {
        return $this->column_name;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function equals(IPartialConstraintModel $partialConstraintModel)
    {
        return $partialConstraintModel->getColumn() == $this->getColumn()
            && $partialConstraintModel->getType() == $this->getType();
    }
}

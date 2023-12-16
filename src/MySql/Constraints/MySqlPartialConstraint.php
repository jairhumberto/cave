<?php

namespace Squille\Cave\MySql\Constraints;

use PDO;
use Squille\Cave\Models\PartialConstraintModelInterface;
use Squille\Cave\MySql\MySqlTable;

class MySqlPartialConstraint implements PartialConstraintModelInterface
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

    public static function selectExpressions(): string
    {
        $allKeys = array_keys(get_class_vars(MySqlPartialConstraint::class));
        $properties = array_filter($allKeys, function ($item) {
            return $item != "pdo" && $item != "table";
        });
        return join(",", $properties);
    }

    public function getName(): string
    {
        return $this->index_name;
    }

    public function __toString()
    {
        return $this->getColumn();
    }

    public function getColumn(): string
    {
        return $this->column_name;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function equals(PartialConstraintModelInterface $partialConstraintModel): bool
    {
        return $partialConstraintModel->getColumn() == $this->getColumn()
            && $partialConstraintModel->getType() == $this->getType();
    }

    public function getType(): string
    {
        return $this->index_type;
    }
}

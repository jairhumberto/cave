<?php

namespace Squille\Cave\MySql\Indexes;

use PDO;
use Squille\Cave\Models\PartialIndexModelInterface;
use Squille\Cave\MySql\MySqlTable;

class MySqlPartialIndex implements PartialIndexModelInterface
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
        $allKeys = array_keys(get_class_vars(MySqlPartialIndex::class));
        $properties = array_filter($allKeys, function ($item) {
            return $item != "pdo" && $item != "table";
        });
        return join(",", $properties);
    }

    public function __toString()
    {
        return $this->getColumn();
    }

    public function getColumn(): string
    {
        return $this->column_name;
    }

    public function getName(): string
    {
        return $this->index_name;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function equals(PartialIndexModelInterface $partialIndexModel): bool
    {
        return $partialIndexModel->getColumn() == $this->getColumn()
            && $partialIndexModel->getType() == $this->getType();
    }

    public function getType(): string
    {
        return $this->index_type;
    }
}

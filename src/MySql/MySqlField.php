<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractFieldModel;
use Squille\Cave\Models\FieldModelInterface;
use Squille\Cave\Unconformity;

class MySqlField extends AbstractFieldModel
{
    private $pdo;
    private $table;
    private $Field;
    private $Type;
    private $Collation;
    private $Null;
    private $Key;
    private $Default;
    private $Extra;
    private $Comment;

    public function __construct(PDO $pdo, MySqlTable $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function getKey()
    {
        return $this->Key;
    }

    public function __toString()
    {
        $columnDefinition = $this->getColumnDefinition();
        return "`{$this->getField()}` $columnDefinition";
    }

    private function getColumnDefinition()
    {
        $columnDefinition = [$this->getType()];
        if ($this->getNull() == "YES") {
            $columnDefinition[] = "NULL";
        } else {
            $columnDefinition[] = "NOT NULL";
        }
        if (strlen($this->getDefault())) {
            $default = $this->defaultFiltered();
            if (!$this->defaultIsFunction() && !is_numeric($default)) {
                $default = "'$default'";
            }
            $columnDefinition[] = "DEFAULT $default";
        }
        if ($this->getExtra()) {
            $columnDefinition[] = $this->getExtra();
        }
        if ($this->getComment()) {
            $columnDefinition[] = "COMMENT '{$this->getComment()}'";
        }
        if ($this->getCollation()) {
            $columnDefinition[] = "COLLATE {$this->getCollation()}";
        }
        return join(" ", $columnDefinition);
    }

    private function defaultFiltered()
    {
        $default = $this->getDefault();
        if (strpos($this->getType(), "bit") !== false) {
            $default = substr($default, 2, -1);
        }
        return $default;
    }

    private function defaultIsFunction()
    {
        return substr($this->getDefault(), -1) == ")";
    }

    protected function typeUnconformity(FieldModelInterface $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} type {{$this->getType()} -> {$fieldModel->getType()}}";
        return new Unconformity($description);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getField()
    {
        return $this->Field;
    }

    public function getType()
    {
        return $this->Type;
    }

    protected function collationUnconformity(FieldModelInterface $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} collate {{$this->getCollation()} -> {$fieldModel->getCollation()}}";
        return new Unconformity($description);
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    protected function nullUnconformity(FieldModelInterface $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} null {{$this->getNull()} -> {$fieldModel->getNull()}}";
        return new Unconformity($description);
    }

    public function getNull()
    {
        return $this->Null;
    }

    protected function defaultUnconformity(FieldModelInterface $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} default {'{$this->getDefault()}' -> '{$fieldModel->getDefault()}'}";
        return new Unconformity($description);
    }

    public function getDefault()
    {
        return $this->Default;
    }

    protected function extraUnconformity(FieldModelInterface $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} extra {{$this->getExtra()} -> {$fieldModel->getExtra()}}";
        return new Unconformity($description);
    }

    public function getExtra()
    {
        return $this->Extra;
    }

    protected function commentUnconformity(FieldModelInterface $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} comment {'{$this->getComment()}' -> '{$fieldModel->getComment()}'}";
        return new Unconformity($description);
    }

    public function getComment()
    {
        return $this->Comment;
    }

    protected function definitionUnconformity(FieldModelInterface $fieldModel)
    {
        $instructions = new InstructionsList();
        $instructions->add(function () use ($fieldModel) {
            $this->pdo->query("
                ALTER TABLE {$fieldModel->getTable()}
                MODIFY $fieldModel
            ");
        });
        return new Unconformity("", $instructions);
    }
}

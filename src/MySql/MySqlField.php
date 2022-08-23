<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlField implements IFieldModel
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

    public function checkIntegrity(IFieldModel $fieldModel)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getType() != $fieldModel->getType()) {
            $unconformities->add($this->typeUnconformity($fieldModel));
        }

        if ($this->getCollation() != $fieldModel->getCollation()) {
            $unconformities->add($this->collationUnconformity($fieldModel));
        }

        if ($this->getNull() != $fieldModel->getNull()) {
            $unconformities->add($this->nullUnconformity($fieldModel));
        }

        if ($this->getDefault() != $fieldModel->getDefault()) {
            $unconformities->add($this->defaultUnconformity($fieldModel));
        }

        if ($this->getExtra() != $fieldModel->getExtra()) {
            $unconformities->add($this->extraUnconformity($fieldModel));
        }

        if ($this->getComment() != $fieldModel->getComment()) {
            $unconformities->add($this->commentUnconformity($fieldModel));
        }

        if ($unconformities->any()) {
            $unconformities->add($this->definitionUnconformity($fieldModel));
        }

        return $unconformities;
    }

    public function getType()
    {
        return $this->Type;
    }

    private function typeUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} type {{$this->getType()} -> {$fieldModel->getType()}}";
        return new Unconformity($description);
    }

    public function getField()
    {
        return $this->Field;
    }

    public function getCollation()
    {
        return $this->Collation;
    }

    private function collationUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} collate {{$this->getCollation()} -> {$fieldModel->getCollation()}}";
        return new Unconformity($description);
    }

    public function getNull()
    {
        return $this->Null;
    }

    private function nullUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} null {{$this->getNull()} -> {$fieldModel->getNull()}}";
        return new Unconformity($description);
    }

    public function getDefault()
    {
        return $this->Default;
    }

    private function defaultUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} default {'{$this->getDefault()}' -> '{$fieldModel->getDefault()}'}";
        return new Unconformity($description);
    }

    public function getExtra()
    {
        return $this->Extra;
    }

    private function extraUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} extra {{$this->getExtra()} -> {$fieldModel->getExtra()}}";
        return new Unconformity($description);
    }

    public function getComment()
    {
        return $this->Comment;
    }

    private function commentUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} comment {'{$this->getComment()}' -> '{$fieldModel->getComment()}'}";
        return new Unconformity($description);
    }

    private function definitionUnconformity(IFieldModel $fieldModel)
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

    public function getTable()
    {
        return $this->table;
    }
}

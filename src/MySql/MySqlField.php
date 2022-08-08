<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlField implements IFieldModel
{
    private $Field;
    private $Type;
    private $Collation;
    private $Null;
    private $Key;
    private $Default;
    private $Extra;
    private $Comment;

    private $pdo;
    private $table;

    public function __construct(PDO $pdo, ITableModel $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(IFieldModel $model)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getType() != $model->getType()) {
            $unconformities->add($this->typeUnconformity($model));
        }

        if ($this->getCollation() != $model->getCollation()) {
            $unconformities->add($this->collationUnconformity($model));
        }

        if ($this->getNull() != $model->getNull()) {
            $unconformities->add($this->nullUnconformity($model));
        }

        if ($this->getDefault() != $model->getDefault()) {
            $unconformities->add($this->defaultUnconformity($model));
        }

        if ($this->getExtra() != $model->getExtra()) {
            $unconformities->add($this->extraUnconformity($model));
        }

        if ($this->getComment() != $model->getComment()) {
            $unconformities->add($this->commentUnconformity($model));
        }

        if ($unconformities->any()) {
            $unconformities->add($this->definitionUnconformity($model));
        }

        return $unconformities;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->Type;
    }

    private function typeUnconformity(IFieldModel $model)
    {
        $description = "alter table modify `{$model->getField()}` type = {`{$this->getType()}` -> `{$model->getType()}`}";
        return new Unconformity($description);
    }

    /**
     * @inheritDoc
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @inheritDoc
     */
    public function getCollation()
    {
        return $this->Collation;
    }

    private function collationUnconformity(IFieldModel $model)
    {
        $description = "alter table modify `{$model->getField()}` collate = {`{$this->getCollation()}` -> `{$model->getCollation()}`}";
        return new Unconformity($description);
    }

    /**
     * @inheritDoc
     */
    public function getNull()
    {
        return $this->Null;
    }

    private function nullUnconformity(IFieldModel $model)
    {
        $description = "alter table modify `{$model->getField()}` null = {{$this->getNull()} -> {$model->getNull()}}";
        return new Unconformity($description);
    }

    /**
     * @inheritDoc
     */
    public function getDefault()
    {
        return $this->Default;
    }

    private function defaultUnconformity(IFieldModel $model)
    {
        $description = "alter table modify `{$model->getField()}` default = {'{$this->getDefault()}' -> '{$model->getDefault()}'}";
        return new Unconformity($description);
    }

    /**
     * @inheritDoc
     */
    public function getExtra()
    {
        return $this->Extra;
    }

    private function extraUnconformity(IFieldModel $model)
    {
        $description = "alter table modify `{$model->getField()}` extra = {`{$this->getExtra()}` -> `{$model->getExtra()}`}";
        return new Unconformity($description);
    }

    /**
     * @inheritDoc
     */
    public function getComment()
    {
        return $this->Comment;
    }

    private function commentUnconformity(IFieldModel $model)
    {
        $description = "alter table modify `{$model->getField()}` comment = {'{$this->getComment()}' -> '{$model->getComment()}'}";
        return new Unconformity($description);
    }

    private function definitionUnconformity(IFieldModel $model)
    {
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("
                ALTER TABLE `{$this->table->getName()}`
                MODIFY $model
            ");
        });
        return new Unconformity("", $instructions);
    }

    /**
     * @inheritDoc
     */
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
        if ($this->getDefault()) {
            $columnDefinition[] = "DEFAULT '{$this->getDefault()}'";
        }
        if ($this->getExtra()) {
            $columnDefinition[] = $this->getExtra();
        }
        if ($this->getComment()) {
            $columnDefinition[] = "COMMENT '{$this->getComment()}'";
        }
        if ($this->getCollation()) {
            $columnDefinition[] = "COLLATE `{$this->getCollation()}`";
        }
        return join(" ", $columnDefinition);
    }
}
